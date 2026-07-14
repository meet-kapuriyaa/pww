@extends('layouts.app')

@section('title', 'Bill of Materials (BOM)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Bill of Materials (BOM)</h1>
        <p class="text-sm text-slate-500">Define raw material requirements and expected waste multipliers for rack manufacturing.</p>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: BOM list -->
        <div class="lg:col-span-2 space-y-6">
            @foreach ($finishedGoods as $good)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">{{ $good->product_name }}</h3>
                            <span class="text-xs text-slate-500 font-mono">SKU: {{ $good->sku }} | List Price: ₹{{ number_format($good->selling_price, 2) }}</span>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">
                            {{ $good->billOfMaterials->count() }} ingredients
                        </span>
                    </div>

                    @if ($good->billOfMaterials->isEmpty())
                        <p class="text-sm text-slate-400 py-2 border border-dashed rounded-lg border-slate-200 text-center">No BOM items defined yet. Use the sidebar panel to add.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase">Raw Material</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-bold text-slate-500 uppercase">Qty Required</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-bold text-slate-500 uppercase">Waste Allowance</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-bold text-slate-500 uppercase">Net Consumption</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($good->billOfMaterials as $bom)
                                        @php
                                            $wasteMultiplier = 1 + ($bom->waste_percentage / 100);
                                            $netConsumption = $bom->required_quantity * $wasteMultiplier;
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $bom->rawMaterial->material_name }}</td>
                                            <td class="px-4 py-3 text-right text-slate-700">{{ number_format($bom->required_quantity, 4) }} {{ $bom->rawMaterial->unit }}</td>
                                            <td class="px-4 py-3 text-right text-rose-600 font-semibold">+{{ number_format($bom->waste_percentage, 1) }}%</td>
                                            <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format($netConsumption, 4) }} {{ $bom->rawMaterial->unit }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Right Col: Add BOM Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-fit">
            <h3 class="text-base font-bold text-slate-800 mb-4">Add BOM Formula Item</h3>
            <form action="{{ route('bom.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Finished Good Product</label>
                    <select name="finished_good_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Finished Rack...</option>
                        @foreach ($finishedGoods as $good)
                            <option value="{{ $good->id }}">{{ $good->product_name }} (SKU: {{ $good->sku }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Raw Material Component</label>
                    <select name="raw_material_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Ingredient...</option>
                        @foreach ($rawMaterials as $mat)
                            <option value="{{ $mat->id }}">{{ $mat->material_name }} ({{ $mat->unit }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Required Qty</label>
                        <input type="number" name="required_quantity" step="0.0001" min="0.0001" placeholder="e.g. 4.5" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Waste Factor (%)</label>
                        <input type="number" name="waste_percentage" step="0.01" min="0" placeholder="e.g. 5%" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded-xl shadow-sm transition duration-150 text-sm">
                    Assign Component
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
