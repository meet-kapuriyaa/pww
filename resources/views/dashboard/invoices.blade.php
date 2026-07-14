@extends('layouts.app')

@section('title', 'Invoice Builder')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            @if ($tab === 'ledger')
                <h1 class="text-2xl font-bold text-slate-800">Corporate Invoices Ledger</h1>
                <p class="text-sm text-slate-500">Review and audit generated tax compliance invoices.</p>
            @elseif ($tab === 'challan-converter')
                <h1 class="text-2xl font-bold text-slate-800">Convert Dispatched Challans</h1>
                <p class="text-sm text-slate-500">Select and merge pending delivery challans into a final invoice.</p>
            @elseif ($tab === 'manual-builder')
                <h1 class="text-2xl font-bold text-slate-800">Direct Custom Invoice Builder</h1>
                <p class="text-sm text-slate-500">Create compliance tax invoices by entering manual items directly.</p>
            @endif
        </div>
    </div>

    <!-- Section 1: Invoices Ledger (Active by default) -->
    <div id="section-ledger" class="{{ $tab === 'ledger' ? 'space-y-6' : 'hidden' }}">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Corporate Invoice Ledger
            </h3>
            
            @if ($invoices->isEmpty())
                <div class="text-center text-slate-400 py-10">No invoices generated yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Invoice No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Destination</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase">Taxable Value</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase">CGST+SGST</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase">IGST</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase">Total Amount</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($invoices as $inv)
                                @php
                                    $pName = 'HQ / Custom';
                                    if ($inv->deliveryChallan && $inv->deliveryChallan->plant) {
                                        $pName = $inv->deliveryChallan->plant->plant_name;
                                    } elseif ($inv->deliveryChallans->isNotEmpty()) {
                                        $pName = $inv->deliveryChallans->first()->plant->plant_name;
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $inv->invoice_number }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $pName }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700">₹{{ number_format($inv->total_taxable_value, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600">
                                        @if ($inv->cgst > 0)
                                            ₹{{ number_format($inv->cgst + $inv->sgst, 2) }}
                                            <span class="text-[9px] block text-slate-400">(9% + 9%)</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600">
                                        @if ($inv->igst > 0)
                                            ₹{{ number_format($inv->igst, 2) }}
                                            <span class="text-[9px] block text-slate-400">(18%)</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800">₹{{ number_format($inv->total_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
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
                <div class="mt-4">
                    {{ $invoices->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Section 2: Convert Challans (Bulk mapping) -->
    <div id="section-challan-converter" class="{{ $tab === 'challan-converter' ? 'space-y-6' : 'hidden' }}">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 max-w-3xl">
            <h3 class="text-base font-bold text-slate-800 mb-2">Convert Dispatched Challans</h3>
            <p class="text-xs text-slate-500 mb-4">Select one or multiple delivery challans dispatched to Balaji Wafers plants to generate a combined tax compliance invoice.</p>
            
            @if ($pendingChallans->isEmpty())
                <div class="p-8 text-center text-slate-400 border border-dashed rounded-lg border-slate-200">
                    No pending delivery challans available for invoicing.
                </div>
            @else
                <form action="{{ route('invoice.create') }}" method="POST" class="ajax-form space-y-4">
                    @csrf
                    <div class="border border-slate-200 rounded-lg divide-y divide-slate-100 max-h-[300px] overflow-y-auto">
                        @foreach ($pendingChallans as $dc)
                            @php
                                $challanVal = 0;
                                foreach ($dc->items as $item) {
                                    $challanVal += $item->quantity * $item->unit_price;
                                }
                            @endphp
                            <div class="p-3.5 bg-slate-50/50 hover:bg-slate-50 flex items-center justify-between text-sm transition">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" name="challan_ids[]" value="{{ $dc->id }}" class="rounded text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span class="font-bold text-slate-800">{{ $dc->challan_number }}</span>
                                        <span class="text-xs text-slate-500 ml-1">({{ $dc->plant->plant_name }})</span>
                                        <div class="text-[10px] text-slate-400">Dispatched: {{ $dc->dispatch_date->format('d M Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-slate-700">₹{{ number_format($challanVal, 2) }}</span>
                                    <div class="text-[10px] text-slate-500">Taxable Value</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="max-w-xs">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Payment Due Date</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <button type="submit" class="bg-theme-blue hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition duration-150 text-sm">
                        Generate Tax Invoice
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Section 3: Manual Custom Invoice Builder (Direct details input) -->
    <div id="section-manual-builder" class="{{ $tab === 'manual-builder' ? 'space-y-6' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Form items editor -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h3 class="text-base font-bold text-slate-800 mb-4">Direct Invoice Itemizer</h3>
                <form id="customInvoiceForm" action="{{ route('invoice.generate') }}" method="POST" class="ajax-form space-y-4 flex-grow">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Number</label>
                            <input type="text" name="invoice_number" value="INV-{{ date('Ymd') }}-{{ rand(100,999) }}" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Destination Plant</label>
                            <select name="plant_id" id="manualPlantSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Choose plant destination...</option>
                                @foreach ($clients as $client)
                                    <optgroup label="{{ $client->company_name }}">
                                        @foreach ($client->plants as $p)
                                            <option value="{{ $p->id }}" data-state="{{ $p->state }}">{{ $p->plant_name }} ({{ $p->state }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                        </div>
                    </div>

                    <!-- Items rows container -->
                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase">Billing Line Items</label>
                            <button type="button" id="addBillingRowBtn" class="text-blue-600 hover:text-blue-700 text-xs font-bold flex items-center">
                                + Add Row
                            </button>
                        </div>

                        <div id="billingRowsContainer" class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                            <!-- Custom Row template -->
                            <div class="billing-row flex items-center space-x-2 bg-slate-50 p-2 rounded-lg border border-slate-200 text-xs">
                                <select name="finished_good_ids[]" class="flex-grow bg-white border border-slate-200 rounded p-1 text-[11px] focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                    <option value="">Select product...</option>
                                    @foreach ($finishedGoods as $g)
                                        <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}">{{ $g->product_name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantities[]" min="1" placeholder="Qty" value="1" class="w-16 bg-white border border-slate-200 rounded p-1 text-[11px] text-right focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price" class="w-20 bg-white border border-slate-200 rounded p-1 text-[11px] text-right focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold">✕</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#1E73BE] hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 text-sm">
                        Generate & Save Invoice
                    </button>
                </form>
            </div>

            <!-- Right Col: Real-time Tax Audit summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-fit text-sm">
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Real-time Tax compliance audit
                </h3>
                
                <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Taxable Value</span>
                        <span class="font-bold text-slate-700" id="live-taxable">₹0.00</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-2">
                        <span class="text-slate-500">CGST (9%)</span>
                        <span class="font-medium text-slate-700" id="live-cgst">₹0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">SGST (9%)</span>
                        <span class="font-medium text-slate-700" id="live-sgst">₹0.00</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-2">
                        <span class="text-slate-500">IGST (18%)</span>
                        <span class="font-medium text-slate-700" id="live-igst">₹0.00</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-300 pt-2 text-base font-black">
                        <span class="text-slate-800">Invoice Total</span>
                        <span class="text-blue-700" id="live-total">₹0.00</span>
                    </div>
                </div>
                
                <div class="mt-4 text-xs text-slate-400 text-center leading-relaxed">
                    Note: regional tax calculations auto-resolve based on whether the destination is in Gujarat (CGST+SGST) or outside (IGST).
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    // Dynamic builder rows & Live Tax Calculations
    const billingRowsContainer = document.getElementById('billingRowsContainer');
    const addBillingRowBtn = document.getElementById('addBillingRowBtn');
    const manualPlantSelect = document.getElementById('manualPlantSelect');
    
    // Add Row
    addBillingRowBtn.addEventListener('click', function() {
        const originalRow = document.querySelector('.billing-row');
        if (!originalRow) return;
        
        const clone = originalRow.cloneNode(true);
        // Reset values
        clone.querySelector('select').value = '';
        clone.querySelector('input[name="quantities[]"]').value = 1;
        clone.querySelector('input[name="unit_prices[]"]').value = '';
        
        // Remove button event
        clone.querySelector('.remove-billing-row-btn').addEventListener('click', function() {
            if (billingRowsContainer.querySelectorAll('.billing-row').length > 1) {
                clone.remove();
                recalculateCustomInvoice();
            }
        });
        
        // Auto set price & Bind recalculate trigger
        clone.querySelector('select').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const price = opt.getAttribute('data-price');
            if (price) {
                clone.querySelector('input[name="unit_prices[]"]').value = price;
            }
            recalculateCustomInvoice();
        });
        
        clone.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', recalculateCustomInvoice);
        });

        billingRowsContainer.appendChild(clone);
        recalculateCustomInvoice();
    });

    // Bind triggers to initial row
    document.querySelector('.billing-row select').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const price = opt.getAttribute('data-price');
        if (price) {
            document.querySelector('.billing-row input[name="unit_prices[]"]').value = price;
        }
        recalculateCustomInvoice();
    });

    document.querySelectorAll('.billing-row input').forEach(input => {
        input.addEventListener('input', recalculateCustomInvoice);
    });

    document.querySelector('.remove-billing-row-btn').addEventListener('click', function() {
        if (billingRowsContainer.querySelectorAll('.billing-row').length > 1) {
            document.querySelector('.billing-row').remove();
            recalculateCustomInvoice();
        }
    });

    // Recalculate tax summary when plant destination changes
    manualPlantSelect.addEventListener('change', recalculateCustomInvoice);

    // Dynamic tax calculation engine
    function recalculateCustomInvoice() {
        const rows = billingRowsContainer.querySelectorAll('.billing-row');
        let totalTaxable = 0.00;
        
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="quantities[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="unit_prices[]"]').value) || 0;
            totalTaxable += qty * price;
        });
        
        // Check destination state
        const selectedPlantOpt = manualPlantSelect.options[manualPlantSelect.selectedIndex];
        const state = selectedPlantOpt ? (selectedPlantOpt.getAttribute('data-state') || '') : '';
        const isGujarat = state.toLowerCase().trim() === 'gujarat';
        
        let cgst = 0.00;
        let sgst = 0.00;
        let igst = 0.00;
        
        if (totalTaxable > 0 && state !== '') {
            if (isGujarat) {
                cgst = Math.round(totalTaxable * 0.09 * 100) / 100;
                sgst = Math.round(totalTaxable * 0.09 * 100) / 100;
            } else {
                igst = Math.round(totalTaxable * 0.18 * 100) / 100;
            }
        }
        
        const total = totalTaxable + cgst + sgst + igst;
        
        // Update DOM
        document.getElementById('live-taxable').innerText = '₹' + totalTaxable.toFixed(2);
        document.getElementById('live-cgst').innerText = '₹' + cgst.toFixed(2);
        document.getElementById('live-sgst').innerText = '₹' + sgst.toFixed(2);
        document.getElementById('live-igst').innerText = '₹' + igst.toFixed(2);
        document.getElementById('live-total').innerText = '₹' + total.toFixed(2);
    }
</script>
@endsection
