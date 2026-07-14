<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RawMaterial;
use App\Models\FinishedGood;
use App\Models\BillOfMaterial;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanItem;
use App\Models\Invoice;
use App\Models\StaffProfile;
use App\Models\LaborLog;
use App\Models\Expense;
use App\Models\ProductionLog;
use App\Services\ProductionService;
use App\Services\BillingService;
use App\Services\PayrollService;
use App\Services\FinancialService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ErpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $productionService;
    protected $billingService;
    protected $payrollService;
    protected $financialService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productionService = resolve(ProductionService::class);
        $this->billingService = resolve(BillingService::class);
        $this->payrollService = resolve(PayrollService::class);
        $this->financialService = resolve(FinancialService::class);
    }

    /**
     * Test the Multi-Stage Stock Auto-Deduction Engine.
     */
    public function test_stock_auto_deduction_engine()
    {
        // Setup User
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@pww.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        // Setup Raw Materials
        $iron = RawMaterial::create([
            'material_name' => 'Iron Coil',
            'unit' => 'kg',
            'current_stock' => 100.00,
            'safety_threshold' => 10.00,
            'average_purchase_price' => 50.00,
        ]);

        // Setup Finished Good
        $rack = FinishedGood::create([
            'product_name' => 'Super Rack',
            'sku' => 'SR-01',
            'current_stock' => 10,
            'selling_price' => 1000.00,
        ]);

        // Setup BOM (Requires 5.0kg of iron per rack, with 10% waste percentage)
        // Consumed per rack = 5.0 * (1 + 10/100) = 5.5kg
        BillOfMaterial::create([
            'finished_good_id' => $rack->id,
            'raw_material_id' => $iron->id,
            'required_quantity' => 5.0,
            'waste_percentage' => 10.00,
        ]);

        // Setup Staff
        $staff = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Worker Amit',
            'wage_type' => 'piece-rate',
            'piece_rate_per_unit' => 20.00,
        ]);

        // Log production of 10 racks
        // Consumed iron: 10 * 5.5 = 55.0kg
        $productionLog = $this->productionService->logProduction(
            $rack->id,
            10, // manufactured
            1,  // rejected
            $user->id,
            Carbon::now()->toDateString(),
            [
                [
                    'staff_profile_id' => $staff->id,
                    'units_completed' => 10
                ]
            ]
        );

        // Assertions
        $this->assertInstanceOf(ProductionLog::class, $productionLog);
        $this->assertEquals(20, FinishedGood::find($rack->id)->current_stock); // 10 initial + 10 made
        $this->assertEquals(45.00, RawMaterial::find($iron->id)->current_stock); // 100 - 55 = 45

        // Verify Labor log created
        $laborLog = LaborLog::where('production_log_id', $productionLog->id)->first();
        $this->assertNotNull($laborLog);
        $this->assertEquals(10, $laborLog->units_completed);
        $this->assertEquals(200.00, $laborLog->calculated_payout); // 10 units * 20.00 rate
        $this->assertEquals('pending', $laborLog->status);

        // Attempt production that exceeds current stock (Requires 10 * 5.5 = 55kg, only 45kg left)
        $this->expectException(InsufficientStockException::class);
        $this->productionService->logProduction(
            $rack->id,
            10,
            0,
            $user->id,
            Carbon::now()->toDateString()
        );
    }

    /**
     * Test the Corporate B2B Billing Module for state tax configurations.
     */
    public function test_regional_gst_billing_logic()
    {
        // Setup B2B Client
        $client = Client::create([
            'company_name' => 'Balaji Wafers',
            'gst_number' => '24AAACB1234A1Z9',
            'corporate_address' => 'Rajkot, Gujarat',
        ]);

        // Gujarat plant (Intra-state)
        $gujaratPlant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Valsad Plant',
            'shipping_address' => 'Valsad, Gujarat',
            'state' => 'Gujarat',
        ]);

        // Indore plant (Inter-state)
        $indorePlant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Indore Plant',
            'shipping_address' => 'Indore, MP',
            'state' => 'Madhya Pradesh',
        ]);

        // Setup Finished Good
        $rack = FinishedGood::create([
            'product_name' => 'Wire Rack',
            'sku' => 'WR-01',
            'current_stock' => 100,
            'selling_price' => 1000.00,
        ]);

        // 1. Test Intrastate (Gujarat) Invoice
        $dc1 = DeliveryChallan::create([
            'client_id' => $client->id,
            'plant_id' => $gujaratPlant->id,
            'challan_number' => 'DC-GUJ-01',
            'dispatch_date' => Carbon::now()->toDateString(),
            'status' => 'pending_invoice',
        ]);

        DeliveryChallanItem::create([
            'delivery_challan_id' => $dc1->id,
            'finished_good_id' => $rack->id,
            'quantity' => 10,
            'unit_price' => 1000.00, // Total taxable: 10,000
        ]);

        $invoiceGuj = $this->billingService->createInvoiceFromChallans([$dc1->id]);

        $this->assertEquals(10000.00, $invoiceGuj->total_taxable_value);
        $this->assertEquals(900.00, $invoiceGuj->cgst); // 9% of 10000
        $this->assertEquals(900.00, $invoiceGuj->sgst); // 9% of 10000
        $this->assertEquals(0.00, $invoiceGuj->igst);
        $this->assertEquals(11800.00, $invoiceGuj->total_amount);
        $this->assertEquals('invoiced', DeliveryChallan::find($dc1->id)->status);

        // 2. Test Interstate (Indore, MP) Invoice
        $dc2 = DeliveryChallan::create([
            'client_id' => $client->id,
            'plant_id' => $indorePlant->id,
            'challan_number' => 'DC-MP-01',
            'dispatch_date' => Carbon::now()->toDateString(),
            'status' => 'pending_invoice',
        ]);

        DeliveryChallanItem::create([
            'delivery_challan_id' => $dc2->id,
            'finished_good_id' => $rack->id,
            'quantity' => 20,
            'unit_price' => 1000.00, // Total taxable: 20,000
        ]);

        $invoiceInd = $this->billingService->createInvoiceFromChallans([$dc2->id]);

        $this->assertEquals(20000.00, $invoiceInd->total_taxable_value);
        $this->assertEquals(0.00, $invoiceInd->cgst);
        $this->assertEquals(0.00, $invoiceInd->sgst);
        $this->assertEquals(3600.00, $invoiceInd->igst); // 18% of 20000
        $this->assertEquals(23600.00, $invoiceInd->total_amount);
        $this->assertEquals('invoiced', DeliveryChallan::find($dc2->id)->status);
    }

    /**
     * Test Payroll compilation and payout mark as paid.
     */
    public function test_payroll_piece_rate_matrix()
    {
        $staff = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Amit Sharma',
            'wage_type' => 'piece-rate',
            'piece_rate_per_unit' => 50.00,
        ]);

        // Mock production log
        $good = FinishedGood::create([
            'product_name' => 'Rack',
            'sku' => 'RK-01',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'm@pww.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $prodLog = ProductionLog::create([
            'finished_good_id' => $good->id,
            'quantity_manufactured' => 100,
            'quantity_rejected' => 0,
            'recorded_by' => $user->id,
            'production_date' => Carbon::now()->toDateString(),
        ]);

        $log1 = LaborLog::create([
            'staff_profile_id' => $staff->id,
            'production_log_id' => $prodLog->id,
            'units_completed' => 40,
            'calculated_payout' => 2000.00,
            'status' => 'pending',
        ]);

        $log2 = LaborLog::create([
            'staff_profile_id' => $staff->id,
            'production_log_id' => $prodLog->id,
            'units_completed' => 20,
            'calculated_payout' => 1000.00,
            'status' => 'pending',
        ]);

        // Compile pending wages
        $compiled = $this->payrollService->compilePendingPieceRateWages();
        $this->assertCount(1, $compiled);
        $this->assertEquals(60, $compiled[0]['total_units_completed']);
        $this->assertEquals(3000.00, $compiled[0]['total_pending_payout']);

        // Mark as paid
        $updatedRows = $this->payrollService->markWagesAsPaid([$log1->id, $log2->id]);
        $this->assertEquals(2, $updatedRows);

        // Compile again (should be empty now)
        $compiledAfter = $this->payrollService->compilePendingPieceRateWages();
        $this->assertCount(0, $compiledAfter);

        $this->assertEquals('paid', LaborLog::find($log1->id)->status);
        $this->assertEquals('paid', LaborLog::find($log2->id)->status);
    }

    /**
     * Test Financial Net Profit Engine calculation framework.
     */
    public function test_financial_profit_engine()
    {
        // 1. Revenue: create paid invoice (excl tax 10,000)
        $inv = Invoice::create([
            'delivery_challan_id' => null,
            'invoice_number' => 'INV-001',
            'total_taxable_value' => 10000.00,
            'cgst' => 900.00,
            'sgst' => 900.00,
            'igst' => 0.00,
            'total_amount' => 11800.00,
            'payment_status' => 'paid',
            'paid_amount' => 11800.00,
            'due_date' => Carbon::now()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        // 2. COGS: mock raw material and production log in range
        $iron = RawMaterial::create([
            'material_name' => 'Wire',
            'unit' => 'kg',
            'current_stock' => 1000.00,
            'safety_threshold' => 10.00,
            'average_purchase_price' => 10.00, // Cost is 10/kg
        ]);

        $good = FinishedGood::create([
            'product_name' => 'Rack',
            'sku' => 'RK-01',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

        BillOfMaterial::create([
            'finished_good_id' => $good->id,
            'raw_material_id' => $iron->id,
            'required_quantity' => 10.0, // 10 kg
            'waste_percentage' => 10.00, // 10% waste => 11 kg consumed per unit
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'm@pww.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        // Manufacturing 10 units => 110 kg iron consumed => COGS = 110 * 10 = ₹1,100
        ProductionLog::create([
            'finished_good_id' => $good->id,
            'quantity_manufactured' => 10,
            'quantity_rejected' => 0,
            'recorded_by' => $user->id,
            'production_date' => Carbon::now()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        // 3. Piece-Rate wages paid: ₹800
        $staff = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Vijay',
            'wage_type' => 'piece-rate',
        ]);

        LaborLog::create([
            'staff_profile_id' => $staff->id,
            'production_log_id' => 1,
            'units_completed' => 40,
            'calculated_payout' => 800.00,
            'status' => 'paid',
            'created_at' => Carbon::now(),
        ]);

        // 4. Logged Overheads: Rent ₹1,500
        Expense::create([
            'expense_category' => 'office_rent',
            'amount' => 1500.00,
            'expense_date' => Carbon::now()->toDateString(),
        ]);

        // 5. Depreciation: Machinery depreciation ₹500
        Expense::create([
            'expense_category' => 'machinery_depreciation',
            'amount' => 500.00,
            'expense_date' => Carbon::now()->toDateString(),
        ]);

        // Calculate expected net profit:
        // Revenue (excl tax): 10,000
        // COGS: 1,100
        // Wages: 800
        // Overheads: 1,500
        // Depreciation: 500
        // Expected Net Profit = 10,000 - (1,100 + 800 + 1,500 + 500) = 10,000 - 3,900 = ₹6,100

        $summary = $this->financialService->getFinancialSummary(
            Carbon::now()->subDay()->toDateString(),
            Carbon::now()->addDay()->toDateString()
        );

        $this->assertEquals(10000.00, $summary['revenue']);
        $this->assertEquals(1100.00, $summary['cogs']);
        $this->assertEquals(800.00, $summary['direct_wages']);
        $this->assertEquals(1500.00, $summary['overheads']);
        $this->assertEquals(500.00, $summary['depreciation']);
        $this->assertEquals(6100.00, $summary['net_profit']);
    }

    /**
     * Test guest redirection to login.
     */
    public function test_guest_redirect_to_login()
    {
        $response = $this->get('/overview');
        $response->assertRedirect('/login');
    }

    /**
     * Test AJAX login verification.
     */
    public function test_ajax_login_flow()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Failed login
        $response = $this->postJson('/login', [
            'email' => 'praful@pww.com',
            'password' => 'wrongpassword'
        ]);
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false
        ]);

        // Successful login
        $response = $this->postJson('/login', [
            'email' => 'praful@pww.com',
            'password' => 'admin123'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('overview')
        ]);
    }

    /**
     * Test AJAX custom direct invoice generation.
     */
    public function test_ajax_custom_invoice_generation()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $client = Client::create([
            'company_name' => 'Balaji Wafers',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Rajkot',
            'state' => 'Gujarat',
        ]);

        $good = FinishedGood::create([
            'product_name' => 'Rack A',
            'sku' => 'RA-01',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

        $response = $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'INV-CUSTOM-999',
            'plant_id' => $plant->id,
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
            'finished_good_ids' => [$good->id],
            'quantities' => [10],
            'unit_prices' => [500],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $invoice = Invoice::where('invoice_number', 'INV-CUSTOM-999')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(5000.00, $invoice->total_taxable_value);
        $this->assertEquals(450.00, $invoice->cgst); // 9%
        $this->assertEquals(450.00, $invoice->sgst); // 9%
        $this->assertEquals(5900.00, $invoice->total_amount);
    }

    /**
     * Test Profile views and settings rendering.
     */
    public function test_profile_settings_view()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('profile'));
        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Update Password');
        $response->assertSee('Back to Panel');
    }

    /**
     * Test AJAX profile details update.
     */
    public function test_ajax_profile_update()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->postJson(route('profile.update'), [
            'name' => 'New Praful Name',
            'email' => 'newemail@pww.com'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $user->refresh();
        $this->assertEquals('New Praful Name', $user->name);
        $this->assertEquals('newemail@pww.com', $user->email);
    }

    /**
     * Test AJAX password modification.
     */
    public function test_ajax_password_update()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Failed password change (wrong current)
        $response = $this->actingAs($user)->postJson(route('profile.password'), [
            'current_password' => 'wrongcurrent',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);
        $response->assertStatus(422);

        // Success password change
        $response = $this->actingAs($user)->postJson(route('profile.password'), [
            'current_password' => 'admin123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $user->password));
    }

    /**
     * Test AJAX mark invoice as paid.
     */
    public function test_ajax_pay_invoice()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-PAYTEST-999',
            'total_taxable_value' => 1000.00,
            'cgst' => 90.00,
            'sgst' => 90.00,
            'igst' => 0.00,
            'total_amount' => 1180.00,
            'payment_status' => 'unpaid',
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($user)->postJson(route('invoice.pay', $invoice->id));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(1180.00, $invoice->paid_amount);
    }

    /**
     * Test Invoice printable tax details sheet rendering.
     */
    public function test_invoice_print_rendering()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $client = Client::create([
            'company_name' => 'Balaji Wafers',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Rajkot plant',
            'state' => 'Gujarat',
        ]);

        $good = FinishedGood::create([
            'product_name' => 'Special Rack X',
            'sku' => 'SRX-99',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

        $challan = DeliveryChallan::create([
            'client_id' => $client->id,
            'plant_id' => $plant->id,
            'challan_number' => 'DC-PRINT-TEST-1',
            'dispatch_date' => Carbon::now()->toDateString(),
            'status' => 'invoiced',
        ]);

        DeliveryChallanItem::create([
            'delivery_challan_id' => $challan->id,
            'finished_good_id' => $good->id,
            'quantity' => 5,
            'unit_price' => 500.00,
        ]);

        $invoice = Invoice::create([
            'delivery_challan_id' => $challan->id,
            'invoice_number' => 'INV-PRINTTEST-999',
            'total_taxable_value' => 2500.00,
            'cgst' => 225.00,
            'sgst' => 225.00,
            'igst' => 0.00,
            'total_amount' => 2950.00,
            'payment_status' => 'unpaid',
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('invoice.print', $invoice->id));
        $response->assertStatus(200);
        $response->assertSee('INV-PRINTTEST-999');
        $response->assertSee('Balaji Wafers');
        $response->assertSee('Rajkot plant');
        $response->assertSee('Special Rack X');
        $response->assertSee('SRX-99');
    }
}
