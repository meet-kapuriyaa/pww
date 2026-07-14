<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\FinishedGood;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanItem;
use App\Models\Invoice;
use App\Models\StaffProfile;
use App\Models\LaborLog;
use App\Models\Expense;
use App\Models\User;
use App\Models\BillOfMaterial;
use App\Models\ProductionLog;
use App\Services\ProductionService;
use App\Services\PayrollService;
use App\Services\BillingService;
use App\Services\FinancialService;
use App\Exceptions\InsufficientStockException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class ErpController extends Controller
{
    protected $productionService;
    protected $payrollService;
    protected $billingService;
    protected $financialService;

    public function __construct(
        ProductionService $productionService,
        PayrollService $payrollService,
        BillingService $billingService,
        FinancialService $financialService
    ) {
        $this->productionService = $productionService;
        $this->payrollService = $payrollService;
        $this->billingService = $billingService;
        $this->financialService = $financialService;
    }

    /**
     * Helper to get standard date range parameters.
     */
    private function getDateRange(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        return [$startDate, $endDate];
    }

    /**
     * 1. Overview Dashboard.
     */
    public function overview(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $financials = $this->financialService->getFinancialSummary($startDate, $endDate);
        $rawMaterials = RawMaterial::all();
        
        // Balaji Wafers plant-wise matrix
        $plants = ClientPlant::whereHas('client', function ($q) {
            $q->where('company_name', 'like', '%Balaji%');
        })->get();

        $plantSalesMatrix = [];
        foreach ($plants as $plant) {
            $sales = Invoice::whereHas('deliveryChallans', function ($q) use ($plant) {
                $q->where('plant_id', $plant->id);
            })->sum('total_taxable_value');

            $freight = Expense::where('expense_category', 'freight_transport')
                ->where('description', 'like', '%' . $plant->plant_name . '%')
                ->sum('amount');

            $plantSalesMatrix[] = [
                'plant_name' => $plant->plant_name,
                'sales' => $sales,
                'freight' => $freight,
            ];
        }

        // Expense distribution
        $expenseCategories = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('expense_category, SUM(amount) as total')
            ->groupBy('expense_category')
            ->get()
            ->pluck('total', 'expense_category')
            ->toArray();

        // 6 months trend
        $trendMonths = [];
        $trendRevenue = [];
        $trendExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $trendMonths[] = $month->format('M Y');
            $trendRevenue[] = Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_taxable_value');
            $trendExpenses[] = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');
        }

        return view('dashboard.overview', compact(
            'financials', 'rawMaterials', 'plantSalesMatrix',
            'expenseCategories', 'trendMonths', 'trendRevenue', 'trendExpenses'
        ));
    }

    /**
     * 2. Inventory Management.
     */
    public function inventory(Request $request)
    {
        $tab = $request->input('tab', 'materials');
        
        $rawMaterials = null;
        $finishedGoods = null;
        
        if ($tab === 'materials') {
            $rawMaterials = RawMaterial::orderBy('material_name')->paginate(20);
        } else {
            $finishedGoods = FinishedGood::orderBy('product_name')->paginate(20);
        }
        
        return view('dashboard.inventory', compact('rawMaterials', 'finishedGoods', 'tab'));
    }

    /**
     * Create Raw Material (AJAX).
     */
    public function storeRawMaterial(Request $request)
    {
        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'safety_threshold' => 'required|numeric|min:0',
            'average_purchase_price' => 'required|numeric|min:0',
        ]);

        $material = RawMaterial::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$material->material_name}' created successfully!",
            'data' => $material
        ]);
    }

    /**
     * Create Finished Good (AJAX).
     */
    public function storeFinishedGood(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|unique:finished_goods,sku|max:100',
            'current_stock' => 'required|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $good = FinishedGood::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Finished Good '{$good->product_name}' cataloged successfully!",
            'data' => $good
        ]);
    }

    /**
     * 3. Bill of Materials (BOM).
     */
    public function bom()
    {
        $finishedGoods = FinishedGood::with('billOfMaterials.rawMaterial')->get();
        $rawMaterials = RawMaterial::all();
        return view('dashboard.bom', compact('finishedGoods', 'rawMaterials'));
    }

    /**
     * Store BOM Item (AJAX).
     */
    public function storeBom(Request $request)
    {
        $validated = $request->validate([
            'finished_good_id' => 'required|exists:finished_goods,id',
            'raw_material_id' => 'required|exists:raw_materials,id',
            'required_quantity' => 'required|numeric|min:0.0001',
            'waste_percentage' => 'required|numeric|min:0',
        ]);

        $bom = BillOfMaterial::updateOrCreate(
            [
                'finished_good_id' => $validated['finished_good_id'],
                'raw_material_id' => $validated['raw_material_id'],
            ],
            [
                'required_quantity' => $validated['required_quantity'],
                'waste_percentage' => $validated['waste_percentage'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "BOM component mapping assigned successfully!",
            'data' => $bom
        ]);
    }

    /**
     * 4. Production Logs.
     */
    public function production()
    {
        $productionLogs = ProductionLog::with('finishedGood', 'recordedByUser')->orderBy('production_date', 'desc')->paginate(20);
        $finishedGoods = FinishedGood::all();
        $staffProfiles = StaffProfile::all();
        $users = User::all();
        return view('dashboard.production', compact('productionLogs', 'finishedGoods', 'staffProfiles', 'users'));
    }

    /**
     * Log a production run (AJAX).
     */
    public function logProduction(Request $request)
    {
        $validated = $request->validate([
            'finished_good_id' => 'required|exists:finished_goods,id',
            'quantity_manufactured' => 'required|integer|min:1',
            'quantity_rejected' => 'required|integer|min:0',
            'recorded_by' => 'required|exists:users,id',
            'production_date' => 'required|date',
            'labor' => 'nullable|array',
        ]);

        try {
            $laborData = [];
            if (!empty($validated['labor'])) {
                foreach ($validated['labor'] as $profileId => $units) {
                    if ($units > 0) {
                        $laborData[] = [
                            'staff_profile_id' => $profileId,
                            'units_completed' => (int) $units
                        ];
                    }
                }
            }

            $log = $this->productionService->logProduction(
                (int) $validated['finished_good_id'],
                (int) $validated['quantity_manufactured'],
                (int) $validated['quantity_rejected'],
                (int) $validated['recorded_by'],
                $validated['production_date'],
                $laborData
            );

            return response()->json([
                'success' => true,
                'message' => "Production batch {$log->id} logged. Stock auto-deductions processed!",
                'data' => $log
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Execution failed: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * 5. Clients & Plants.
     */
    public function clients()
    {
        $clients = Client::with('plants')->orderBy('company_name')->get();
        return view('dashboard.clients', compact('clients'));
    }

    /**
     * Create Client (AJAX).
     */
    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'gst_number' => 'required|string|max:50',
            'corporate_address' => 'required|string',
        ]);

        $client = Client::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Client profile '{$client->company_name}' registered successfully!",
            'data' => $client
        ]);
    }

    /**
     * Create Plant (AJAX).
     */
    public function storePlant(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plant_name' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'shipping_address' => 'required|string',
        ]);

        $plant = ClientPlant::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Client Plant '{$plant->plant_name}' created successfully!",
            'data' => $plant
        ]);
    }

    /**
     * 6. Delivery Challans.
     */
    public function challans()
    {
        $deliveryChallans = DeliveryChallan::with('client', 'plant', 'items.finishedGood')->orderBy('dispatch_date', 'desc')->paginate(20);
        $clients = Client::with('plants')->get();
        $finishedGoods = FinishedGood::all();
        return view('dashboard.challans', compact('deliveryChallans', 'clients', 'finishedGoods'));
    }

    /**
     * Store Delivery Challan (AJAX).
     */
    public function storeChallan(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plant_id' => 'required|exists:client_plants,id',
            'challan_number' => 'required|string|unique:delivery_challans,challan_number',
            'dispatch_date' => 'required|date',
            'finished_good_ids' => 'required|array|min:1',
            'finished_good_ids.*' => 'required|exists:finished_goods,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
        ]);

        try {
            $challan = DB::transaction(function () use ($validated) {
                $dc = DeliveryChallan::create([
                    'client_id' => $validated['client_id'],
                    'plant_id' => $validated['plant_id'],
                    'challan_number' => $validated['challan_number'],
                    'dispatch_date' => $validated['dispatch_date'],
                    'status' => 'pending_invoice',
                ]);

                foreach ($validated['finished_good_ids'] as $idx => $fgId) {
                    DeliveryChallanItem::create([
                        'delivery_challan_id' => $dc->id,
                        'finished_good_id' => $fgId,
                        'quantity' => $validated['quantities'][$idx],
                        'unit_price' => $validated['unit_prices'][$idx],
                    ]);
                }

                return $dc;
            });

            return response()->json([
                'success' => true,
                'message' => "Delivery Challan '{$challan->challan_number}' recorded successfully!",
                'data' => $challan
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to log challan: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * 7. Invoices & Billing.
     */
    public function invoices(Request $request)
    {
        $tab = $request->input('tab', 'ledger');
        $invoices = Invoice::with(['deliveryChallans.plant', 'deliveryChallan.client'])->orderBy('created_at', 'desc')->paginate(20);
        $pendingChallans = DeliveryChallan::where('status', 'pending_invoice')->with('client', 'plant', 'items.finishedGood')->get();
        $finishedGoods = FinishedGood::all();
        $clients = Client::with('plants')->get();
        return view('dashboard.invoices', compact('invoices', 'pendingChallans', 'finishedGoods', 'clients', 'tab'));
    }

    /**
     * Generate Invoice from Challans (AJAX).
     */
    public function createInvoice(Request $request)
    {
        $validated = $request->validate([
            'challan_ids' => 'required|array|min:1',
            'challan_ids.*' => 'required|exists:delivery_challans,id',
            'due_date' => 'nullable|date',
        ]);

        try {
            $invoice = $this->billingService->createInvoiceFromChallans(
                array_map('intval', $validated['challan_ids']),
                $validated['due_date']
            );

            return response()->json([
                'success' => true,
                'message' => "Compliance Tax Invoice '{$invoice->invoice_number}' created successfully!",
                'data' => $invoice
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 422);
        }
    }

    /**
     * Generate Custom Direct Invoice (AJAX).
     */
    public function generateCustomInvoice(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'plant_id' => 'required|exists:client_plants,id',
            'due_date' => 'required|date',
            'finished_good_ids' => 'required|array|min:1',
            'finished_good_ids.*' => 'required|exists:finished_goods,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated) {
                $plant = ClientPlant::findOrFail($validated['plant_id']);
                $isGujarat = strcasecmp(trim($plant->state), 'Gujarat') === 0;

                // Calculate taxable subtotal
                $taxable = 0.00;
                foreach ($validated['finished_good_ids'] as $idx => $fgId) {
                    $taxable += $validated['quantities'][$idx] * $validated['unit_prices'][$idx];
                }

                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;

                if ($isGujarat) {
                    $cgst = round($taxable * 0.09, 2);
                    $sgst = round($taxable * 0.09, 2);
                } else {
                    $igst = round($taxable * 0.18, 2);
                }

                $total = $taxable + $cgst + $sgst + $igst;

                return Invoice::create([
                    'delivery_challan_id' => null, // custom direct
                    'invoice_number' => $validated['invoice_number'],
                    'total_taxable_value' => $taxable,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'igst' => $igst,
                    'total_amount' => $total,
                    'payment_status' => 'unpaid',
                    'paid_amount' => 0.00,
                    'due_date' => $validated['due_date'],
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Custom Tax Invoice '{$invoice->invoice_number}' logged successfully!",
                'data' => $invoice
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to log invoice: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * 8. Employees Directory.
     */
    public function employees()
    {
        $staffProfiles = StaffProfile::orderBy('full_name')->paginate(20);
        return view('dashboard.employees', compact('staffProfiles'));
    }

    /**
     * Create Employee Profile (AJAX).
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'wage_type' => 'required|in:fixed,piece-rate',
            'monthly_salary' => 'nullable|required_if:wage_type,fixed|numeric|min:0',
            'piece_rate_per_unit' => 'nullable|required_if:wage_type,piece-rate|numeric|min:0',
        ]);

        $staff = StaffProfile::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee profile for '{$staff->full_name}' created successfully!",
            'data' => $staff
        ]);
    }

    /**
     * Disburse payroll (AJAX).
     */
    public function payPayroll(Request $request)
    {
        $validated = $request->validate([
            'labor_log_ids' => 'required|array|min:1',
            'labor_log_ids.*' => 'required|exists:labor_logs,id',
        ]);

        try {
            $count = $this->payrollService->markWagesAsPaid($validated['labor_log_ids']);
            return response()->json([
                'success' => true,
                'message' => "Successfully paid compiled wages for {$count} logged runs!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 9. Operational Expenses.
     */
    public function expenses()
    {
        $expenses = Expense::orderBy('expense_date', 'desc')->paginate(20);
        return view('dashboard.expenses', compact('expenses'));
    }

    /**
     * Log Expense (AJAX).
     */
    public function logExpense(Request $request)
    {
        $validated = $request->validate([
            'expense_category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $expense = Expense::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Expense logged successfully in category '" . str_replace('_', ' ', $expense->expense_category) . "'!",
            'data' => $expense
        ]);
    }

    /**
     * 10. Reports Page.
     */
    public function reports(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $financials = $this->financialService->getFinancialSummary($startDate, $endDate);
        return view('dashboard.reports', compact('startDate', 'endDate', 'financials'));
    }

    /**
     * Export Reports Data to CSV.
     */
    public function exportCsv(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $financials = $this->financialService->getFinancialSummary($startDate, $endDate);
        $invoices = Invoice::whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])->get();
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->get();

        $response = new StreamedResponse(function() use ($startDate, $endDate, $financials, $invoices, $expenses) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Section 1: Title
            fputcsv($handle, ['PRAFUL WELDING WORKS (PWW) - FINANCIAL AUDIT REPORT']);
            fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
            fputcsv($handle, []);

            // Section 2: Statement of Profit & Loss
            fputcsv($handle, ['STATEMENT OF NET PROFIT & LOSS']);
            fputcsv($handle, ['Line Item', 'Accounting Detail', 'Amount (INR)']);
            fputcsv($handle, ['Total Sales Revenue (A)', 'Taxable invoiced amounts (excl. GST)', $financials['revenue']]);
            fputcsv($handle, ['Cost of Goods Sold (B)', 'Weighted raw material stock consumption + waste', $financials['cogs']]);
            fputcsv($handle, ['Direct Wages Paid (C)', 'Piece-rate labor log disbursements', $financials['direct_wages']]);
            fputcsv($handle, ['Operational Overheads (D)', 'Electricity, gas, rent, admin, transport', $financials['overheads']]);
            fputcsv($handle, ['Machinery Depreciation (E)', 'Wear and tear schedules', $financials['depreciation']]);
            fputcsv($handle, ['NET CORPORATE PROFIT', 'Calculation: A - B - C - D - E', $financials['net_profit']]);
            fputcsv($handle, ['Gross profit margin (%)', 'Margin ratio', $financials['gross_profit_margin'] . '%']);
            fputcsv($handle, []);

            // Section 3: Invoices Audit Ledger
            fputcsv($handle, ['INVOICES LEDGER AUDIT']);
            fputcsv($handle, ['Invoice No', 'Taxable Value', 'CGST', 'SGST', 'IGST', 'Total Amount', 'Due Date', 'Status']);
            foreach ($invoices as $inv) {
                fputcsv($handle, [
                    $inv->invoice_number,
                    $inv->total_taxable_value,
                    $inv->cgst,
                    $inv->sgst,
                    $inv->igst,
                    $inv->total_amount,
                    $inv->due_date->toDateString(),
                    $inv->payment_status
                ]);
            }
            fputcsv($handle, []);

            // Section 4: Expenses audit ledger
            fputcsv($handle, ['EXPENSES LEDGER AUDIT']);
            fputcsv($handle, ['Date', 'Expense Category', 'Memo Description', 'Amount (INR)']);
            foreach ($expenses as $exp) {
                fputcsv($handle, [
                    $exp->expense_date->toDateString(),
                    ucwords(str_replace('_', ' ', $exp->expense_category)),
                    $exp->description,
                    $exp->amount
                ]);
            }

            fclose($handle);
        });

        $filename = "PWW-ERP-Audit-Report-{$startDate}-to-{$endDate}.csv";
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Trigger Database Re-seeding for demonstration.
     */
    public function resetData()
    {
        try {
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            
            // Automatically log in the seed user to maintain session after migrate:fresh
            $seedUser = User::where('email', 'praful@pww.com')->first();
            if ($seedUser) {
                auth()->login($seedUser);
            }

            return response()->json([
                'success' => true,
                'message' => 'Database reset and seeded with production-grade demo data successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Database reset failed: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * 11. Profile Management.
     */
    public function profile()
    {
        return view('dashboard.profile');
    }

    /**
     * Update Profile Information.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile information updated successfully!'
        ]);
    }

    /**
     * Update Password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['The provided current password does not match our records.']]
            ], 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['new_password'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!'
        ]);
    }
}
