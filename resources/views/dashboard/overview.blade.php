@extends('layouts.app')

@section('title', 'Overview Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Overview Dashboard</h1>
            <p class="text-sm text-slate-500">Live operational snapshot of Praful Welding Works.</p>
        </div>
        <div class="text-sm text-slate-400 font-semibold uppercase tracking-wider">
            As of: {{ date('d M Y') }}
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Revenue (30d)</span>
                <p class="text-xl font-black text-slate-800 mt-0.5">₹{{ number_format($financials['revenue'], 2) }}</p>
            </div>
        </div>

        <!-- Expenses Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total COGS + Overheads</span>
                <p class="text-xl font-black text-slate-800 mt-0.5">₹{{ number_format($financials['cogs'] + $financials['overheads'] + $financials['direct_wages'], 2) }}</p>
            </div>
        </div>

        <!-- Net Profit Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Net Profit (30d)</span>
                <p class="text-xl font-black text-emerald-800 mt-0.5">₹{{ number_format($financials['net_profit'], 2) }}</p>
            </div>
        </div>

        <!-- Receivables Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 8h.01M19 12a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Receivables</span>
                <p class="text-xl font-black text-amber-800 mt-0.5">₹{{ number_format($financials['outstanding_receivables'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Alert Banner for Low Stock (if any) -->
    @php
        $lowStockItems = $rawMaterials->filter(fn($mat) => $mat->current_stock < $mat->safety_threshold);
    @endphp
    @if ($lowStockItems->isNotEmpty())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-sm flex items-start space-x-3">
            <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <h4 class="font-bold text-sm">Critical Inventory Alert:</h4>
                <p class="text-xs text-rose-700 mt-1">The following raw materials are currently below safety threshold limits. Please reorder stock immediately:</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach ($lowStockItems as $item)
                        <span class="px-2 py-0.5 bg-rose-200 text-rose-900 text-xs font-bold rounded-lg">{{ $item->material_name }} ({{ number_format($item->current_stock, 1) }} {{ $item->unit }} left)</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Charts Dashboard Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Line Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 lg:col-span-2 flex flex-col">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Revenue vs Expenses Trend (Last 6 Months)
            </h3>
            <div class="relative flex-grow min-h-[250px]">
                <canvas id="revenueExpensesChart"></canvas>
            </div>
        </div>

        <!-- Donut Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                Expense Distribution
            </h3>
            <div class="relative flex-grow min-h-[250px] flex items-center justify-center">
                @if (empty($expenseCategories))
                    <div class="text-center text-slate-400 py-10">No expenses recorded in this period.</div>
                @else
                    <canvas id="expenseCategoryChart"></canvas>
                @endif
            </div>
        </div>
    </div>

    <!-- Plant-wise Sales Matrix -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Balaji Wafers Client Plants sales matrix
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Plant Location</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales Invoiced (Excl. Tax)</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Freight Cost</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Freight-to-Sales Ratio</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @foreach ($plantSalesMatrix as $matrix)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">{{ $matrix['plant_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">₹{{ number_format($matrix['sales'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">₹{{ number_format($matrix['freight'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($matrix['sales'] > 0)
                                    <span class="font-bold text-blue-600">{{ number_format(($matrix['freight'] / $matrix['sales']) * 100, 2) }}%</span>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Line Chart
    const ctxLine = document.getElementById('revenueExpensesChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: @json($trendMonths),
            datasets: [
                {
                    label: 'Taxable Sales',
                    data: @json($trendRevenue),
                    borderColor: '#1E73BE',
                    backgroundColor: 'rgba(30, 115, 190, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                },
                {
                    label: 'Expenses',
                    data: @json($trendExpenses),
                    borderColor: '#707A8A',
                    backgroundColor: 'rgba(112, 122, 138, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Outfit', size: 11 } } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(226, 232, 240, 0.8)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Donut Chart
    @if (!empty($expenseCategories))
    const ctxDonut = document.getElementById('expenseCategoryChart').getContext('2d');
    const expenseData = @json($expenseCategories);
    const categoryLabels = Object.keys(expenseData).map(label => {
        return label.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    });

    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: Object.values(expenseData),
                backgroundColor: ['#1e73be', '#707a8a', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#f43f5e'],
                borderWidth: 1.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { family: 'Outfit', size: 9 } } }
            },
            cutout: '70%'
        }
    });
    @endif
</script>
@endsection
