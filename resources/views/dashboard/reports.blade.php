@extends('layouts.app')

@section('title', 'Reports & Export')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Operational & Financial Reports</h1>
            <p class="text-sm text-slate-500">Analyze PWW profit margins, COGS, and download compliant CSV audit spreadsheets.</p>
        </div>
        
        <!-- CSV Export Button -->
        <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
           class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Export Reports Ledger (CSV)</span>
        </a>
    </div>

    <!-- Date Range Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="grid grid-cols-2 gap-4 flex-grow max-w-xl">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>
            <button type="submit" class="bg-theme-blue hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition duration-150 text-sm whitespace-nowrap">
                Filter Audit Period
            </button>
        </form>
    </div>

    <!-- Financial Ledger Balance Sheet -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Statement of Net Profit / Loss
        </h3>
        
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Accounting Item</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <!-- Revenue -->
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800">Total Sales Revenue (A)</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">Sum of taxable values of all generated compliance invoices. (Excludes tax)</td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-600">₹{{ number_format($financials['revenue'], 2) }}</td>
                    </tr>
                    <!-- COGS -->
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800">Cost of Goods Sold (B)</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">Weighted cost of raw materials consumed during production (including waste %).</td>
                        <td class="px-6 py-4 text-right font-bold text-rose-600">- ₹{{ number_format($financials['cogs'], 2) }}</td>
                    </tr>
                    <!-- Wages -->
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800">Direct Wages Paid (C)</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">Total piece-rate payouts logged for manual fabrication labor.</td>
                        <td class="px-6 py-4 text-right font-bold text-rose-600">- ₹{{ number_format($financials['direct_wages'], 2) }}</td>
                    </tr>
                    <!-- Overheads -->
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800">Logged Overheads (D)</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">Electricity, industrial welding gas refills, rent, and office supplies.</td>
                        <td class="px-6 py-4 text-right font-bold text-rose-600">- ₹{{ number_format($financials['overheads'], 2) }}</td>
                    </tr>
                    <!-- Depreciation -->
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800">Machinery Depreciation (E)</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">Calculated depreciation values recorded against welding machinery.</td>
                        <td class="px-6 py-4 text-right font-bold text-rose-600">- ₹{{ number_format($financials['depreciation'], 2) }}</td>
                    </tr>
                    <!-- Net Profit -->
                    <tr class="bg-slate-50 font-bold border-t-2 border-slate-300">
                        <td class="px-6 py-4 text-slate-800 text-base">Net Profit / Loss (A - B - C - D - E)</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">PWW net corporate earnings for this audit period.</td>
                        <td class="px-6 py-4 text-right text-base {{ $financials['net_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            ₹{{ number_format($financials['net_profit'], 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
