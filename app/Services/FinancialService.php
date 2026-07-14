<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\ProductionLog;
use App\Models\LaborLog;
use App\Models\Expense;
use App\Models\RawMaterial;
use Carbon\Carbon;

class FinancialService
{
    /**
     * Get the financial summary across a date range.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array
     */
    public function getFinancialSummary($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Total Invoiced Revenue (excl. Tax)
        $revenue = Invoice::whereBetween('created_at', [$start, $end])
            ->sum('total_taxable_value');

        // 2. Cost of Consumed Raw Materials
        // Get production logs in this date range
        $productionLogs = ProductionLog::whereBetween('production_date', [$start, $end])
            ->with(['finishedGood.billOfMaterials.rawMaterial'])
            ->get();

        $cogs = 0.00;
        foreach ($productionLogs as $log) {
            $finishedGood = $log->finishedGood;
            if (!$finishedGood) {
                continue;
            }
            foreach ($finishedGood->billOfMaterials as $bom) {
                $rawMaterial = $bom->rawMaterial;
                if (!$rawMaterial) {
                    continue;
                }
                
                // Total Consumed = quantity_manufactured * required_quantity * (1 + (waste_percentage / 100))
                $wasteMultiplier = 1 + ($bom->waste_percentage / 100);
                $consumed = $log->quantity_manufactured * $bom->required_quantity * $wasteMultiplier;
                
                // Material Cost = Consumed * average_purchase_price
                $cogs += $consumed * $rawMaterial->average_purchase_price;
            }
        }

        // 3. Direct Piece-Rate Wages Paid
        $directWages = LaborLog::whereBetween('created_at', [$start, $end])
            ->sum('calculated_payout');

        // 4. Logged Overheads (Expenses except machinery depreciation)
        $overheads = Expense::whereBetween('expense_date', [$start, $end])
            ->where('expense_category', '!=', 'machinery_depreciation')
            ->sum('amount');

        // 5. Scheduled Machinery Depreciation
        $depreciation = Expense::whereBetween('expense_date', [$start, $end])
            ->where('expense_category', 'machinery_depreciation')
            ->sum('amount');

        // Net Profit = Revenue - (COGS + Direct Wages + Overheads + Depreciation)
        $netProfit = $revenue - ($cogs + $directWages + $overheads + $depreciation);

        // Gross Profit Margin (%) = ((Revenue - COGS) / Revenue) * 100
        $grossProfit = $revenue - $cogs;
        $grossProfitMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0.00;

        // Outstanding Accounts Receivable
        $outstandingReceivables = Invoice::whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->selectRaw('SUM(total_amount - paid_amount) as outstanding')
            ->value('outstanding') ?? 0.00;

        return [
            'revenue' => round($revenue, 2),
            'cogs' => round($cogs, 2),
            'direct_wages' => round($directWages, 2),
            'overheads' => round($overheads, 2),
            'depreciation' => round($depreciation, 2),
            'net_profit' => round($netProfit, 2),
            'gross_profit' => round($grossProfit, 2),
            'gross_profit_margin' => round($grossProfitMargin, 2),
            'outstanding_receivables' => round($outstandingReceivables, 2),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }
}
