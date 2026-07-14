@extends('layouts.app')

@section('title', 'Inventory Audit')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            @if ($tab === 'materials')
                <h1 class="text-2xl font-bold text-slate-800">Raw Materials Inventory Audit</h1>
                <p class="text-sm text-slate-500">Track, manage, and audit factory raw material supplies.</p>
            @else
                <h1 class="text-2xl font-bold text-slate-800">Finished Goods Catalog</h1>
                <p class="text-sm text-slate-500">Audit and catalog completed welding racks.</p>
            @endif
        </div>
    </div>

    <!-- Active Tab Content -->
    @if ($tab === 'materials')
        
        <!-- 1. INSERT FORM AT THE TOP -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Add Raw Material
            </h3>
            <form action="{{ route('inventory.materials.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Material Name</label>
                        <input type="text" name="material_name" placeholder="e.g. Iron Wire Coils (5mm)" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Measurement Unit</label>
                        <input type="text" name="unit" placeholder="e.g. kg, liters, packs" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Current Stock Quantity</label>
                        <input type="number" name="current_stock" step="0.0001" min="0" placeholder="e.g. 15000" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Safety Threshold Alert Limit</label>
                        <input type="number" name="safety_threshold" step="0.0001" min="0" placeholder="e.g. 2000" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Average Purchase Price (₹)</label>
                        <input type="number" name="average_purchase_price" step="0.01" min="0" placeholder="e.g. 85.00" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                    Create Raw Material
                </button>
            </form>
        </div>

        <!-- 2. RECORDS LIST UNDERNEATH -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Raw Materials Ledger
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Material Name</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Current Stock</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Safety Threshold Limit</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Average Price</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($rawMaterials as $mat)
                            @php $isLow = $mat->current_stock < $mat->safety_threshold; @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $mat->material_name }}</td>
                                <td class="px-6 py-4 text-right font-medium text-slate-700">{{ number_format($mat->current_stock, 2) }} {{ $mat->unit }}</td>
                                <td class="px-6 py-4 text-right text-slate-500">{{ number_format($mat->safety_threshold, 1) }} {{ $mat->unit }}</td>
                                <td class="px-6 py-4 text-right text-slate-700">₹{{ number_format($mat->average_purchase_price, 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($isLow)
                                        <span class="px-2.5 py-0.5 bg-rose-50 border border-rose-200 text-rose-700 text-[10px] rounded font-bold uppercase tracking-wider animate-pulse">Low Stock</span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] rounded font-bold uppercase tracking-wider">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $rawMaterials->appends(request()->query())->links() }}
            </div>
        </div>

    @else
        
        <!-- 1. INSERT FORM AT THE TOP -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Add Finished Good Rack
            </h3>
            <form action="{{ route('inventory.goods.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Product Model Name</label>
                        <input type="text" name="product_name" placeholder="e.g. Balaji Wire Rack 3-Tier" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SKU Code (Unique)</label>
                        <input type="text" name="sku" placeholder="e.g. WR-3T-BALAJI" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Opening Stock Inventory</label>
                        <input type="number" name="current_stock" min="0" placeholder="e.g. 50" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Corporate Selling Price (Excl. Tax)</label>
                        <input type="number" name="selling_price" step="0.01" min="0" placeholder="e.g. 1850.00" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                    Catalog Finished Good
                </button>
            </form>
        </div>

        <!-- 2. RECORDS LIST UNDERNEATH -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Finished Goods Catalog
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Product Name</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">SKU</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Current Stock</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Selling Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($finishedGoods as $good)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $good->product_name }}</td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ $good->sku }}</td>
                                <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $good->current_stock }} units</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-850">₹{{ number_format($good->selling_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $finishedGoods->appends(request()->query())->links() }}
            </div>
        </div>

    @endif
</div>
@endsection
