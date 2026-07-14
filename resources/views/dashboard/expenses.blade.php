@extends('layouts.app')

@section('title', 'Expenses Ledger')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Expenses Ledger</h1>
        <p class="text-sm text-slate-500">Record factory overheads, transport freight, office administration costs, and machinery depreciation.</p>
    </div>

    <!-- 1. INSERT FORM AT THE TOP -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Log Factory Overheads
        </h3>
        <form action="{{ route('expense.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Expense Category</label>
                    <select name="expense_category" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="factory_electricity">Factory Electricity</option>
                        <option value="industrial_gas">Industrial Gas / Consumables</option>
                        <option value="welding_consumables">Welding Consumables</option>
                        <option value="freight_transport">Freight & Transport Charges</option>
                        <option value="office_rent">Office / Factory Rent</option>
                        <option value="administrative">Administrative Expenses</option>
                        <option value="machinery_depreciation">Machinery Depreciation Schedule</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Amount (₹)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="₹ Value" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Expense Date</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Memo / Detail Description</label>
                <textarea name="description" rows="2" placeholder="Additional details (e.g. transport allocation)..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"></textarea>
            </div>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                Record Expense Ledger
            </button>
        </form>
    </div>

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Logged Operational Expenses
        </h3>
        
        @if ($expenses->isEmpty())
            <div class="text-center text-slate-400 py-10">No expense records found.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Expense Date</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Category</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Memo / Description</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($expenses as $exp)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $exp->expense_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-slate-700 font-semibold capitalize">{{ str_replace('_', ' ', $exp->expense_category) }}</td>
                                <td class="px-6 py-4 text-slate-500 max-w-[300px] truncate">{{ $exp->description ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-right font-bold text-rose-600">₹{{ number_format($exp->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $expenses->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
