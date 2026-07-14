<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWW ERP - Owner's Executive Dashboard</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .theme-blue { color: #1E73BE; }
        .bg-theme-blue { background-color: #1E73BE; }
        .border-theme-gray { border-color: #707A8A; }
        .text-theme-gray { color: #707A8A; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <!-- PWW interlocking branding SVG -->
                <svg class="h-10 w-auto" viewBox="0 0 500 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- P -->
                    <path d="M40 30h110c40 0 70 20 70 60s-30 60-70 60H90v60H40V30zm50 80h60c15 0 25-8 25-20s-10-20-25-20H90v40z" fill="#1E73BE"/>
                    <!-- Gray interlocking accent -->
                    <path d="M120 85l60 90-25 40-60-90 25-40z" fill="#707A8A" opacity="0.9"/>
                    <!-- W -->
                    <path d="M185 30l35 180h40l25-110 25 110h40l35-180h-45l-20 115-25-115H250l-25 115-20-115h-45z" fill="#1E73BE"/>
                </svg>
                <div class="flex flex-col">
                    <span class="text-xl font-bold tracking-tight text-slate-800">Praful Welding Works</span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-theme-gray">ERP Enterprise Dashboard</span>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <form action="{{ route('reset-data') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset all records to default demo data?');">
                    @csrf
                    <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium py-2 px-4 rounded-lg border border-slate-200 transition duration-150 ease-in-out">
                        Reset Demo Data
                    </button>
                </form>
                <div class="flex items-center space-x-2 text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span class="font-medium text-slate-700">MySQL Connection: Active</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center shadow-sm" role="alert">
                <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex flex-col shadow-sm" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-bold">Execution Failed:</span>
                </div>
                <ul class="list-disc list-inside mt-2 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filter Date & Dashboard Stats Overview -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Owner's Executive Summary</h1>
                    <p class="text-sm text-theme-gray">Praful Welding Works financial health, margins, and key inventory metrics.</p>
                </div>
                <form method="GET" class="flex items-center space-x-3 bg-slate-50 p-2 rounded-xl border border-slate-200">
                    <div class="flex items-center space-x-2">
                        <label class="text-xs font-semibold text-slate-600 uppercase">From:</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="bg-white border border-slate-200 rounded-lg text-sm px-2.5 py-1 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-xs font-semibold text-slate-600 uppercase">To:</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="bg-white border border-slate-200 rounded-lg text-sm px-2.5 py-1 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="bg-theme-blue hover:bg-blue-700 text-white text-xs font-semibold py-1.5 px-4 rounded-lg transition duration-150">
                        Apply Filter
                    </button>
                </form>
            </div>

            <!-- Financial Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl border border-blue-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-700">Gross Revenue</span>
                    <p class="text-xl font-bold text-blue-900 mt-1">₹{{ number_format($financials['revenue'], 2) }}</p>
                    <span class="text-[10px] text-blue-600">Excl. Tax</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-theme-gray">Raw Material COGS</span>
                    <p class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($financials['cogs'], 2) }}</p>
                    <span class="text-[10px] text-slate-500">Consumed stock + waste</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-theme-gray">Piece-Rate Wages</span>
                    <p class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($financials['direct_wages'], 2) }}</p>
                    <span class="text-[10px] text-slate-500">Paid & accrued labor</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-theme-gray">Overhead Expenses</span>
                    <p class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($financials['overheads'], 2) }}</p>
                    <span class="text-[10px] text-slate-500">Excl. depreciation</span>
                </div>
                <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Net Profit</span>
                    <p class="text-xl font-bold text-emerald-950 mt-1">₹{{ number_format($financials['net_profit'], 2) }}</p>
                    <span class="text-[10px] text-emerald-700">Margin: {{ number_format($financials['gross_profit_margin'], 1) }}%</span>
                </div>
                <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800">Receivables</span>
                    <p class="text-xl font-bold text-amber-950 mt-1">₹{{ number_format($financials['outstanding_receivables'], 2) }}</p>
                    <span class="text-[10px] text-amber-700">Outstanding B2B</span>
                </div>
            </div>
        </div>

        <!-- Charts Dashboard Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Line Chart (Monthly Revenue vs Expenses) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 lg:col-span-2 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Monthly Revenue vs Expenses Trend
                </h3>
                <div class="relative flex-grow min-h-[300px]">
                    <canvas id="revenueExpensesChart"></canvas>
                </div>
            </div>

            <!-- Expense Category Donut Chart -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    Expense Distribution
                </h3>
                <div class="relative flex-grow min-h-[300px] flex items-center justify-center">
                    @if (empty($expenseCategories))
                        <div class="text-center text-slate-400 py-10">No expense records found in this range.</div>
                    @else
                        <canvas id="expenseCategoryChart"></canvas>
                    @endif
                </div>
            </div>
        </div>

        <!-- B2B Ecosystem: Balaji Wafers plant sales & freight transport matrix -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Balaji Wafers Plant Sales & Dedicated Freight Matrix
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Client Plant Location</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales Revenue (Excl. Tax)</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Dedicated Freight & Logistics Cost</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Logistics-to-Sales Ratio</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Shipping Route Tax Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @foreach ($plantSalesMatrix as $matrix)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                                    {{ $matrix['plant_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    ₹{{ number_format($matrix['sales'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    ₹{{ number_format($matrix['freight'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    @if ($matrix['sales'] > 0)
                                        <span class="font-medium text-blue-600">{{ number_format(($matrix['freight'] / $matrix['sales']) * 100, 2) }}%</span>
                                    @else
                                        <span class="text-slate-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($matrix['plant_name'] === 'Indore Plant')
                                        <span class="px-2 py-1 bg-purple-50 text-purple-700 border border-purple-200 text-xs rounded-full font-medium">Interstate (18% IGST)</span>
                                    @else
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 border border-blue-200 text-xs rounded-full font-medium">Gujarat Intrastate (9% CGST + 9% SGST)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Middle Grid: Stock Auto-Deduction Engine & Current Inventories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Log Production Form (Multi-Stage Stock Auto-Deduction Engine) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Log Finished Goods Production Run
                </h3>
                <form action="{{ route('production.log') }}" method="POST" class="space-y-4 flex-grow">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Finished Good Product</label>
                            <select name="finished_good_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select product...</option>
                                @foreach ($finishedGoods as $good)
                                    <option value="{{ $good->id }}">{{ $good->product_name }} (SKU: {{ $good->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Production Date</label>
                            <input type="date" name="production_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty Manufactured</label>
                            <input type="number" name="quantity_manufactured" min="1" placeholder="e.g. 50" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty Rejected</label>
                            <input type="number" name="quantity_rejected" min="0" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Recorded By Manager</label>
                            <select name="recorded_by" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @foreach ($users->whereIn('role', ['admin', 'manager']) as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Staff Piece-Rate Work Log Allocation</label>
                        <div class="space-y-2 max-h-[140px] overflow-y-auto bg-slate-50 p-3 rounded-lg border border-slate-200">
                            @foreach ($staffProfiles->where('wage_type', 'piece-rate') as $staff)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-700">{{ $staff->full_name }} (₹{{ $staff->piece_rate_per_unit }}/unit)</span>
                                    <div class="flex items-center space-x-2">
                                        <input type="number" name="labor[{{ $staff->id }}]" min="0" placeholder="Units Done" class="w-24 bg-white border border-slate-200 rounded px-2 py-0.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <span class="text-xs text-slate-400">units</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-theme-blue hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-150 ease-in-out text-sm">
                        Execute Production Run & Auto-Deduct Stock
                    </button>
                </form>
            </div>

            <!-- Inventories Stock Status & Warnings -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Real-time Inventory Audit
                </h3>
                <div class="space-y-6 flex-grow">
                    <!-- Raw Materials -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Raw Materials (Weighted Stock)</h4>
                        <div class="space-y-2">
                            @foreach ($rawMaterials as $mat)
                                @php $isLow = $mat->current_stock < $mat->safety_threshold; @endphp
                                <div class="flex items-center justify-between p-2.5 rounded-lg border {{ $isLow ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200' }} text-sm transition">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="font-medium {{ $isLow ? 'text-rose-900 font-bold' : 'text-slate-800' }}">{{ $mat->material_name }}</span>
                                        @if ($isLow)
                                            <span class="px-2 py-0.5 bg-rose-200 text-rose-800 text-[10px] rounded-full font-bold uppercase tracking-wider animate-pulse">Low Stock Warning</span>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold {{ $isLow ? 'text-rose-700' : 'text-slate-700' }}">{{ number_format($mat->current_stock, 1) }} {{ $mat->unit }}</span>
                                        <div class="text-[10px] text-slate-500">Safety Threshold: {{ number_format($mat->safety_threshold, 0) }} {{ $mat->unit }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Finished Goods -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Finished Goods (Racks & Stands)</h4>
                        <div class="space-y-2">
                            @foreach ($finishedGoods as $good)
                                <div class="flex items-center justify-between p-2.5 rounded-lg border bg-slate-50 border-slate-200 text-sm">
                                    <div>
                                        <span class="font-medium text-slate-800">{{ $good->product_name }}</span>
                                        <div class="text-[10px] text-slate-500">SKU: {{ $good->sku }} | List Price: ₹{{ number_format($good->selling_price, 2) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-slate-700">{{ $good->current_stock }} units</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lower Grid: B2B Invoicing & Payroll Payout Matrix -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Corporate Billing: Convert Challans to Tax Compliance Invoice -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    B2B Invoicing Hub (Balaji Wafers)
                </h3>
                <p class="text-xs text-theme-gray mb-4">Combine dispatched delivery challans into a final tax compliance invoice with regional GST calculation.</p>
                
                @if ($pendingChallans->isEmpty())
                    <div class="flex-grow flex items-center justify-center p-8 bg-slate-50 rounded-xl border border-slate-200 border-dashed text-slate-400">
                        No pending delivery challans available for invoicing.
                    </div>
                @else
                    <form action="{{ route('invoice.create') }}" method="POST" class="space-y-4 flex-grow">
                        @csrf
                        <div class="max-h-[220px] overflow-y-auto border border-slate-200 rounded-lg divide-y divide-slate-100">
                            @foreach ($pendingChallans as $dc)
                                @php
                                    $challanValue = 0;
                                    foreach ($dc->items as $item) {
                                        $challanValue += $item->quantity * $item->unit_price;
                                    }
                                @endphp
                                <div class="p-3 bg-slate-50/50 hover:bg-slate-50 flex items-start justify-between text-sm transition">
                                    <div class="flex items-start space-x-3">
                                        <input type="checkbox" name="challan_ids[]" value="{{ $dc->id }}" class="mt-1 rounded text-blue-600 focus:ring-blue-500">
                                        <div>
                                            <span class="font-semibold text-slate-800">{{ $dc->challan_number }}</span>
                                            <span class="text-xs text-slate-500 ml-1">({{ $dc->plant->plant_name }})</span>
                                            <div class="text-[10px] text-slate-400">Dispatched: {{ $dc->dispatch_date->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-slate-700">₹{{ number_format($challanValue, 2) }}</span>
                                        <div class="text-[10px] text-slate-500">Taxable Value</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <button type="submit" class="w-full bg-theme-blue hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-150 text-sm">
                            Generate Tax Compliance Invoice & Calculate GST
                        </button>
                    </form>
                @endif
            </div>

            <!-- Piece-rate wages compilation & Payout Matrix -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Wages Matrix & Payroll Ledger
                </h3>
                <p class="text-xs text-theme-gray mb-4">Auto-compiling pending wage payouts for piece-rate workers based on completed manufacturing outputs.</p>

                @if ($pendingWages->isEmpty())
                    <div class="flex-grow flex items-center justify-center p-8 bg-slate-50 rounded-xl border border-slate-200 border-dashed text-slate-400">
                        All piece-rate worker payouts are compiled and paid up-to-date!
                    </div>
                @else
                    <form action="{{ route('payroll.pay') }}" method="POST" class="space-y-4 flex-grow flex flex-col justify-between">
                        @csrf
                        <div class="space-y-3 flex-grow max-h-[220px] overflow-y-auto pr-1">
                            @foreach ($pendingWages as $payout)
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-3">
                                        @foreach ($payout['log_ids'] as $lid)
                                            <input type="hidden" name="labor_log_ids[]" value="{{ $lid }}">
                                        @endforeach
                                        <div>
                                            <span class="font-bold text-slate-800">{{ $payout['full_name'] }}</span>
                                            <span class="text-xs text-slate-500 ml-1">(Piece-rate)</span>
                                            <div class="text-[10px] text-slate-500">Output: {{ $payout['total_units_completed'] }} units logged at ₹{{ $payout['piece_rate_per_unit'] }}/unit</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-blue-700">₹{{ number_format($payout['total_pending_payout'], 2) }}</span>
                                        <div class="text-[10px] text-slate-500">{{ $payout['pending_logs_count'] }} logs pending</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-150 text-sm mt-4">
                            Approve & Disburse Compiled Wages
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Bottom Grid: Expenses logging & Invoice compliance audit logs -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Log Expenses / Overheads Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col lg:col-span-1">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Log Overheads & Depreciation
                </h3>
                <form action="{{ route('expense.log') }}" method="POST" class="space-y-4 flex-grow">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Expense Category</label>
                        <select name="expense_category" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="factory_electricity">Factory Electricity</option>
                            <option value="industrial_gas">Industrial Gas / Consumables</option>
                            <option value="welding_consumables">Welding Consumables (Rods, Wire)</option>
                            <option value="freight_transport">Freight & Transport Charges</option>
                            <option value="office_rent">Office / Factory Rent</option>
                            <option value="administrative">Administrative Expenses</option>
                            <option value="machinery_depreciation">Machinery Depreciation Schedule</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Amount (₹)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" placeholder="e.g. 5500" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Expense Date</label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description / Memo</label>
                        <input type="text" name="description" placeholder="Include plant name (e.g. Rajkot Plant) for freight" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-150 text-sm">
                        Record Expense
                    </button>
                </form>
            </div>

            <!-- Tax Invoice Compliance Ledger -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Corporate Tax Invoice Ledger
                </h3>
                <div class="overflow-y-auto max-h-[300px] flex-grow">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">Invoice No</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">Destination</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-bold text-slate-500 uppercase">Taxable</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-bold text-slate-500 uppercase">CGST+SGST</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-bold text-slate-500 uppercase">IGST</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-bold text-slate-500 uppercase">Total</th>
                                <th scope="col" class="px-4 py-2 text-center text-xs font-bold text-slate-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100 text-xs">
                            @foreach ($invoices as $inv)
                                @php
                                    $plantName = 'HQ / General';
                                    if ($inv->deliveryChallan && $inv->deliveryChallan->plant) {
                                        $plantName = $inv->deliveryChallan->plant->plant_name;
                                    } elseif ($inv->deliveryChallans->isNotEmpty()) {
                                        $plantName = $inv->deliveryChallans->first()->plant->plant_name;
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-800">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                        {{ $plantName }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">
                                        ₹{{ number_format($inv->total_taxable_value, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-slate-600">
                                        @if ($inv->cgst > 0)
                                            ₹{{ number_format($inv->cgst + $inv->sgst, 2) }}
                                            <span class="text-[9px] block text-slate-400">(9% + 9%)</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-slate-600">
                                        @if ($inv->igst > 0)
                                            ₹{{ number_format($inv->igst, 2) }}
                                            <span class="text-[9px] block text-slate-400">(18%)</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-slate-800">
                                        ₹{{ number_format($inv->total_amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                            {{ $inv->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 
                                               ($inv->payment_status === 'partially_paid' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                            {{ $inv->payment_status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-theme-gray">
            © 2026 Praful Welding Works (PWW). Designed for robust, enterprise-grade B2B engineering steel rack ERP compliance.
        </div>
    </footer>

    <!-- Chart Implementation Scripts -->
    <script>
        // Line Chart setup
        const ctxLine = document.getElementById('revenueExpensesChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: @json($trendMonths),
                datasets: [
                    {
                        label: 'Taxable Invoiced Sales',
                        data: @json($trendRevenue),
                        borderColor: '#1E73BE',
                        backgroundColor: 'rgba(30, 115, 190, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#1E73BE'
                    },
                    {
                        label: 'Operational Expenses',
                        data: @json($trendExpenses),
                        borderColor: '#707A8A',
                        backgroundColor: 'rgba(112, 122, 138, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#707A8A'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Outfit',
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.8)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Donut Chart setup
        @if (!empty($expenseCategories))
        const ctxDonut = document.getElementById('expenseCategoryChart').getContext('2d');
        const expenseData = @json($expenseCategories);
        
        // Clean categories names
        const categoryLabels = Object.keys(expenseData).map(label => {
            return label.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        });

        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: Object.values(expenseData),
                    backgroundColor: [
                        '#1e73be', // dominant corporate blue
                        '#707a8a', // slate grey
                        '#f59e0b', // amber
                        '#10b981', // green
                        '#ec4899', // pink
                        '#8b5cf6', // purple
                        '#f43f5e'  // rose
                    ],
                    borderWidth: 1.5,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 8,
                            font: {
                                family: 'Outfit',
                                size: 10
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
        @endif
    </script>
</body>
</html>
