<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Core Users
        $adminUser = User::create([
            'name' => 'hardik  vekariya',
            'email' => 'pww@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $managerUser = User::create([
            'name' => 'Sanjay Shah',
            'email' => 'sanjay@pww.com',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'status' => 'active',
        ]);

        $accountantUser = User::create([
            'name' => 'Ramesh Mehta',
            'email' => 'ramesh@pww.com',
            'password' => Hash::make('acc123'),
            'role' => 'accountant',
            'status' => 'active',
        ]);

        $staffUser1 = User::create([
            'name' => 'Amit Sharma',
            'email' => 'amit@pww.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'status' => 'active',
        ]);

        $staffUser2 = User::create([
            'name' => 'Rajesh Patel',
            'email' => 'rajesh@pww.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'status' => 'active',
        ]);

        // 2. Create Raw Materials
        $ironWire = RawMaterial::create([
            'material_name' => 'Iron Wire Coils (5mm)',
            'unit' => 'kg',
            'current_stock' => 12500.0000,
            'safety_threshold' => 2000.0000,
            'average_purchase_price' => 82.50,
        ]);

        $powderPaint = RawMaterial::create([
            'material_name' => 'Powder Paint (Industrial Blue)',
            'unit' => 'kg',
            'current_stock' => 650.0000,
            'safety_threshold' => 150.0000,
            'average_purchase_price' => 310.00,
        ]);

        $weldingRods = RawMaterial::create([
            'material_name' => 'Welding Consumables (Rods)',
            'unit' => 'packs',
            'current_stock' => 85.0000,
            'safety_threshold' => 15.0000,
            'average_purchase_price' => 450.00,
        ]);

        $co2Gas = RawMaterial::create([
            'material_name' => 'CO2 Shielding Gas',
            'unit' => 'liters',
            'current_stock' => 420.0000,
            'safety_threshold' => 80.0000,
            'average_purchase_price' => 120.00,
        ]);

        // 3. Create Finished Goods
        $rack3Tier = FinishedGood::create([
            'product_name' => 'Balaji Wire Rack 3-Tier',
            'sku' => 'WR-3T-BALAJI',
            'current_stock' => 150,
            'selling_price' => 1850.00,
            'alerts_enabled' => true,
        ]);

        $rack4Tier = FinishedGood::create([
            'product_name' => 'Balaji Wire Rack 4-Tier',
            'sku' => 'WR-4T-BALAJI',
            'current_stock' => 90,
            'selling_price' => 2400.00,
            'alerts_enabled' => true,
        ]);

        // 4. Create BOM
        // Rack 3-Tier requires 4.5kg iron, 0.3kg paint, 0.5l gas
        BillOfMaterial::create([
            'finished_good_id' => $rack3Tier->id,
            'raw_material_id' => $ironWire->id,
            'required_quantity' => 4.5000,
            'waste_percentage' => 5.00,
        ]);
        BillOfMaterial::create([
            'finished_good_id' => $rack3Tier->id,
            'raw_material_id' => $powderPaint->id,
            'required_quantity' => 0.3000,
            'waste_percentage' => 10.00,
        ]);
        BillOfMaterial::create([
            'finished_good_id' => $rack3Tier->id,
            'raw_material_id' => $co2Gas->id,
            'required_quantity' => 0.5000,
            'waste_percentage' => 2.00,
        ]);

        // Rack 4-Tier requires 6kg iron, 0.4kg paint, 0.7l gas
        BillOfMaterial::create([
            'finished_good_id' => $rack4Tier->id,
            'raw_material_id' => $ironWire->id,
            'required_quantity' => 6.0000,
            'waste_percentage' => 6.00,
        ]);
        BillOfMaterial::create([
            'finished_good_id' => $rack4Tier->id,
            'raw_material_id' => $powderPaint->id,
            'required_quantity' => 0.4000,
            'waste_percentage' => 10.00,
        ]);
        BillOfMaterial::create([
            'finished_good_id' => $rack4Tier->id,
            'raw_material_id' => $co2Gas->id,
            'required_quantity' => 0.7000,
            'waste_percentage' => 2.00,
        ]);

        // 5. Create B2B Client & Plants
        $client = Client::create([
            'company_name' => 'Balaji Wafers HQ',
            'gst_number' => '24AAACB1234A1Z9',
            'corporate_address' => 'Vajdi GIDC, Kalawad Road, Rajkot - 360021, Gujarat, India',
        ]);

        $plantRajkot = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Rajkot Plant',
            'shipping_address' => 'Plot 22-25, Sector 3, Metoda GIDC, Rajkot, Gujarat',
            'state' => 'Gujarat',
        ]);

        $plantValsad = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Valsad Plant',
            'shipping_address' => 'Survey No. 412/1, Gundlav GIDC, Valsad, Gujarat',
            'state' => 'Gujarat',
        ]);

        $plantIndore = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Indore Plant',
            'shipping_address' => 'Sector I, Pithampur Industrial Area, Indore, Madhya Pradesh',
            'state' => 'Madhya Pradesh',
        ]);

        // 6. Create Staff Profiles
        $staff1 = StaffProfile::create([
            'user_id' => $staffUser1->id,
            'full_name' => 'Amit Sharma',
            'wage_type' => 'piece-rate',
            'piece_rate_per_unit' => 45.00,
        ]);

        $staff2 = StaffProfile::create([
            'user_id' => $staffUser2->id,
            'full_name' => 'Rajesh Patel',
            'wage_type' => 'piece-rate',
            'piece_rate_per_unit' => 45.00,
        ]);

        $staff3 = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Sunita Rao',
            'wage_type' => 'fixed',
            'monthly_salary' => 22000.00,
        ]);

        // 7. Seed Operational Expenses over past 6 months
        $categories = [
            'factory_electricity', 'industrial_gas', 'welding_consumables',
            'freight_transport', 'office_rent', 'administrative', 'machinery_depreciation'
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i)->day(rand(1, 28));

            Expense::create([
                'expense_category' => 'factory_electricity',
                'amount' => rand(15000, 22000),
                'expense_date' => $date->toDateString(),
                'description' => 'Monthly Factory Power Bill - PGECL',
            ]);

            Expense::create([
                'expense_category' => 'industrial_gas',
                'amount' => rand(8000, 14000),
                'expense_date' => $date->copy()->addDays(5)->toDateString(),
                'description' => 'Argon/CO2 Cylinder Refills',
            ]);

            Expense::create([
                'expense_category' => 'office_rent',
                'amount' => 12000.00,
                'expense_date' => $date->copy()->startOfMonth()->toDateString(),
                'description' => 'Factory shed monthly lease',
            ]);

            Expense::create([
                'expense_category' => 'machinery_depreciation',
                'amount' => 4500.00,
                'expense_date' => $date->copy()->endOfMonth()->toDateString(),
                'description' => 'Calculated monthly wear on welding stations & cutters',
            ]);

            Expense::create([
                'expense_category' => 'administrative',
                'amount' => rand(3000, 6000),
                'expense_date' => $date->toDateString(),
                'description' => 'Office consumables & refreshments',
            ]);

            // Add dedicated freight expenses for Balaji Wafers plants (helps in the Sales-Freight matrix)
            Expense::create([
                'expense_category' => 'freight_transport',
                'amount' => rand(4000, 6000),
                'expense_date' => $date->copy()->addDays(2)->toDateString(),
                'description' => "Freight Charges to {$plantRajkot->plant_name}",
            ]);
            Expense::create([
                'expense_category' => 'freight_transport',
                'amount' => rand(6500, 9500),
                'expense_date' => $date->copy()->addDays(12)->toDateString(),
                'description' => "Freight Charges to {$plantValsad->plant_name}",
            ]);
            Expense::create([
                'expense_category' => 'freight_transport',
                'amount' => rand(11000, 15000),
                'expense_date' => $date->copy()->addDays(22)->toDateString(),
                'description' => "Freight Charges to {$plantIndore->plant_name}",
            ]);
        }

        // 8. Log Historical Production & Labor over the past 3 months
        for ($i = 2; $i >= 0; $i--) {
            $prodDate = Carbon::now()->subMonths($i)->subDays(15);
            
            // Log 3-tier production run
            $quantity3T = rand(80, 120);
            $rejected3T = rand(2, 6);
            $prodLog3T = ProductionLog::create([
                'finished_good_id' => $rack3Tier->id,
                'quantity_manufactured' => $quantity3T,
                'quantity_rejected' => $rejected3T,
                'recorded_by' => $managerUser->id,
                'production_date' => $prodDate->toDateString(),
                'created_at' => $prodDate,
            ]);

            // Staff labor logs for this run
            LaborLog::create([
                'staff_profile_id' => $staff1->id,
                'production_log_id' => $prodLog3T->id,
                'units_completed' => ceil($quantity3T / 2),
                'calculated_payout' => ceil($quantity3T / 2) * $staff1->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate,
            ]);
            LaborLog::create([
                'staff_profile_id' => $staff2->id,
                'production_log_id' => $prodLog3T->id,
                'units_completed' => floor($quantity3T / 2),
                'calculated_payout' => floor($quantity3T / 2) * $staff2->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate,
            ]);

            // Log 4-tier production run
            $quantity4T = rand(50, 80);
            $rejected4T = rand(1, 4);
            $prodLog4T = ProductionLog::create([
                'finished_good_id' => $rack4Tier->id,
                'quantity_manufactured' => $quantity4T,
                'quantity_rejected' => $rejected4T,
                'recorded_by' => $managerUser->id,
                'production_date' => $prodDate->copy()->addDays(5)->toDateString(),
                'created_at' => $prodDate->copy()->addDays(5),
            ]);

            // Staff labor logs for 4-tier
            LaborLog::create([
                'staff_profile_id' => $staff1->id,
                'production_log_id' => $prodLog4T->id,
                'units_completed' => ceil($quantity4T / 2),
                'calculated_payout' => ceil($quantity4T / 2) * $staff1->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate->copy()->addDays(5),
            ]);
            LaborLog::create([
                'staff_profile_id' => $staff2->id,
                'production_log_id' => $prodLog4T->id,
                'units_completed' => floor($quantity4T / 2),
                'calculated_payout' => floor($quantity4T / 2) * $staff2->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate->copy()->addDays(5),
            ]);
        }

        // 9. Generate Historical Delivery Challans & Invoices
        $plantsArray = [$plantRajkot, $plantValsad, $plantIndore];
        
        foreach ($plantsArray as $index => $plant) {
            $dispatchDate = Carbon::now()->subDays(20 - ($index * 5));
            $challanNo = 'DC-' . date('Ymd') . '-00' . ($index + 1);

            $dc = DeliveryChallan::create([
                'client_id' => $client->id,
                'plant_id' => $plant->id,
                'challan_number' => $challanNo,
                'dispatch_date' => $dispatchDate->toDateString(),
                'status' => 'invoiced',
            ]);

            // Add items to challan
            $qty3T = rand(20, 40);
            $qty4T = rand(15, 30);
            
            DeliveryChallanItem::create([
                'delivery_challan_id' => $dc->id,
                'finished_good_id' => $rack3Tier->id,
                'quantity' => $qty3T,
                'unit_price' => $rack3Tier->selling_price,
            ]);

            DeliveryChallanItem::create([
                'delivery_challan_id' => $dc->id,
                'finished_good_id' => $rack4Tier->id,
                'quantity' => $qty4T,
                'unit_price' => $rack4Tier->selling_price,
            ]);

            // Invoice for the challan
            $taxable = ($qty3T * $rack3Tier->selling_price) + ($qty4T * $rack4Tier->selling_price);
            $cgst = 0.00;
            $sgst = 0.00;
            $igst = 0.00;

            if ($plant->state === 'Gujarat') {
                $cgst = round($taxable * 0.09, 2);
                $sgst = round($taxable * 0.09, 2);
            } else {
                $igst = round($taxable * 0.18, 2);
            }

            $total = $taxable + $cgst + $sgst + $igst;
            $invNo = 'INV-' . $dispatchDate->format('Ymd') . '-00' . ($index + 1);
            
            $invoice = Invoice::create([
                'delivery_challan_id' => $dc->id,
                'invoice_number' => $invNo,
                'total_taxable_value' => $taxable,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'total_amount' => $total,
                'payment_status' => ($index === 0) ? 'paid' : (($index === 1) ? 'partially_paid' : 'unpaid'),
                'paid_amount' => ($index === 0) ? $total : (($index === 1) ? round($total / 2, 2) : 0.00),
                'due_date' => $dispatchDate->copy()->addDays(30)->toDateString(),
                'created_at' => $dispatchDate,
            ]);

            $dc->update(['invoice_id' => $invoice->id]);
        }

        // 10. Generate Pending Delivery Challans (for UI demonstrations)
        foreach ($plantsArray as $index => $plant) {
            $dispatchDate = Carbon::now()->subDays(2);
            $challanNo = 'DC-PENDING-00' . ($index + 1);

            $dc = DeliveryChallan::create([
                'client_id' => $client->id,
                'plant_id' => $plant->id,
                'challan_number' => $challanNo,
                'dispatch_date' => $dispatchDate->toDateString(),
                'status' => 'pending_invoice',
            ]);

            DeliveryChallanItem::create([
                'delivery_challan_id' => $dc->id,
                'finished_good_id' => $rack3Tier->id,
                'quantity' => 15,
                'unit_price' => $rack3Tier->selling_price,
            ]);

            DeliveryChallanItem::create([
                'delivery_challan_id' => $dc->id,
                'finished_good_id' => $rack4Tier->id,
                'quantity' => 10,
                'unit_price' => $rack4Tier->selling_price,
            ]);
        }

        // 11. Create a few pending labor log entries for wage compilation test
        $pendingProdLog = ProductionLog::create([
            'finished_good_id' => $rack3Tier->id,
            'quantity_manufactured' => 60,
            'quantity_rejected' => 1,
            'recorded_by' => $managerUser->id,
            'production_date' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        LaborLog::create([
            'staff_profile_id' => $staff1->id,
            'production_log_id' => $pendingProdLog->id,
            'units_completed' => 30,
            'calculated_payout' => 30 * $staff1->piece_rate_per_unit,
            'status' => 'pending',
        ]);

        LaborLog::create([
            'staff_profile_id' => $staff2->id,
            'production_log_id' => $pendingProdLog->id,
            'units_completed' => 30,
            'calculated_payout' => 30 * $staff2->piece_rate_per_unit,
            'status' => 'pending',
        ]);
    }
}

