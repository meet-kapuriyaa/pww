@extends('layouts.app')

@section('title', 'Delivery Challans')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Delivery Challans</h1>
        <p class="text-sm text-slate-500">Record rack dispatches and manage pending delivery challans before final invoicing.</p>
    </div>

    <!-- 1. INSERT FORM AT THE TOP -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Dispatch Delivery Challan
        </h3>
        <form action="{{ route('challans.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Corporate Client</label>
                    <select name="client_id" id="clientSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select B2B client...</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shipping Plant Address</label>
                    <select name="plant_id" id="plantSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Choose plant location...</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Challan Number</label>
                    <input type="text" name="challan_number" value="DC-{{ date('Ymd') }}-{{ rand(100, 999) }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Dispatch Date</label>
                    <input type="date" name="dispatch_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <!-- Challan Item dynamic rows adder -->
            <div class="border-t border-slate-200 pt-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Items Dispatched</label>
                    <button type="button" id="addRowBtn" class="text-blue-600 hover:text-blue-700 text-xs font-bold flex items-center">
                        + Add Item Row
                    </button>
                </div>
                
                <div id="itemsContainer" class="space-y-2.5">
                    <!-- Row Template -->
                    <div class="item-row flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-sm">
                        <div class="flex-grow">
                            <select name="finished_good_ids[]" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Rack Finished Good...</option>
                                @foreach ($finishedGoods as $g)
                                    <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}">{{ $g->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-32">
                            <input type="number" name="quantities[]" min="1" placeholder="Quantity" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="w-full md:w-40">
                            <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price per unit" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <button type="button" class="remove-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 py-1 text-sm">Remove</button>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                Record Dispatch Challan
            </button>
        </form>
    </div>

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
            Dispatched Challans Ledger
        </h3>
        
        @if ($deliveryChallans->isEmpty())
            <div class="text-center text-slate-400 py-10">No delivery challans recorded yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Challan No</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Dispatch Date</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Destination Client & Plant</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Total Items</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($deliveryChallans as $dc)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $dc->challan_number }}</td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $dc->dispatch_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-slate-700">
                                    <span class="font-bold text-slate-800">{{ $dc->client->company_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-slate-500 block">({{ $dc->plant->plant_name ?? 'N/A' }})</span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-slate-600">{{ $dc->items->sum('quantity') }} units</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($dc->status === 'invoiced')
                                        <span class="px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] rounded font-bold uppercase tracking-wider">Invoiced</span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-amber-50 border border-amber-200 text-amber-700 text-[10px] rounded font-bold uppercase tracking-wider">Pending Invoice</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $deliveryChallans->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Dynamic filtering and rows adder script -->
<script>
    // 1. Client-Plant dynamic listing
    const plantsData = @json($clients->mapWithKeys(function($c) {
        return [$c->id => $c->plants->map(function($p) {
            return ['id' => $p->id, 'name' => $p->plant_name];
        })];
    }));
    
    const clientSelect = document.getElementById('clientSelect');
    const plantSelect = document.getElementById('plantSelect');
    
    clientSelect.addEventListener('change', function() {
        const clientId = this.value;
        plantSelect.innerHTML = '<option value="">Choose plant location...</option>';
        
        if (clientId && plantsData[clientId]) {
            plantsData[clientId].forEach(plant => {
                const opt = document.createElement('option');
                opt.value = plant.id;
                opt.innerText = plant.name;
                plantSelect.appendChild(opt);
            });
        }
    });

    // 2. Dynamic rows adder
    const itemsContainer = document.getElementById('itemsContainer');
    const addRowBtn = document.getElementById('addRowBtn');
    
    // Add new item row
    addRowBtn.addEventListener('click', function() {
        const originalRow = document.querySelector('.item-row');
        if (!originalRow) return;
        
        const clone = originalRow.cloneNode(true);
        // Reset values
        clone.querySelector('select').value = '';
        clone.querySelectorAll('input').forEach(input => input.value = '');
        
        // Add remove listener
        clone.querySelector('.remove-row-btn').addEventListener('click', function() {
            if (itemsContainer.querySelectorAll('.item-row').length > 1) {
                clone.remove();
            }
        });
        
        // Auto price setting on rack selection
        clone.querySelector('select').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const price = opt.getAttribute('data-price');
            if (price) {
                clone.querySelector('input[name="unit_prices[]"]').value = price;
            }
        });

        itemsContainer.appendChild(clone);
    });

    // Initial event bindings
    document.querySelector('.item-row select').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const price = opt.getAttribute('data-price');
        if (price) {
            document.querySelector('.item-row input[name="unit_prices[]"]').value = price;
        }
    });

    document.querySelector('.remove-row-btn').addEventListener('click', function() {
        if (itemsContainer.querySelectorAll('.item-row').length > 1) {
            document.querySelector('.item-row').remove();
        }
    });
</script>
@endsection
