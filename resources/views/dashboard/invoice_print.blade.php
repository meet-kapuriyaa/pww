<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: 'Outfit', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .print-border {
                border: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .invoice-card {
                box-shadow: none !important;
                border: none !important;
                padding: 2mm !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-6">

    <!-- Print Control Bar (Hidden on print) -->
    <div class="max-w-4xl mx-auto no-print mb-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold text-slate-600">Tax Invoice View (A4 Print-Optimized)</span>
            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                {{ $invoice->payment_status }}
            </span>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-sm text-xs transition">
                Print / Save as PDF
            </button>
            <button onclick="window.close()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-1.5 px-3 rounded-lg text-xs transition">
                Close
            </button>
        </div>
    </div>

    <!-- Main Invoice Sheet container -->
    <div class="max-w-4xl mx-auto bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8 invoice-card print-border">
        
        <!-- Top header: Brand and Invoice Meta -->
        <div class="flex flex-row justify-between items-center border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('logo.jpg') }}" alt="PWW Logo" class="w-10 h-10 object-contain rounded-lg border border-slate-100">
                <div>
                    <h1 class="text-base font-extrabold text-slate-800 uppercase tracking-tight leading-none">Praful Welding Works</h1>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1">Heavy Fabrication & Industrial Racks ERP</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Tax Invoice</h2>
                <p class="text-lg font-black text-slate-800 mt-1 leading-none">{{ $invoice->invoice_number }}</p>
            </div>
        </div>

        <!-- Meta Details grid: Vendor, Client, Dates -->
        <div class="grid grid-cols-3 gap-6 text-xs mb-6 border-b border-slate-100 pb-4">
            <!-- Vendor (PWW) -->
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Seller (Issued By)</span>
                <div class="font-bold text-slate-800">Praful Welding Works</div>
                <div class="text-[10px] text-slate-500 mt-0.5 leading-relaxed">
                    Plot No. 12, G.I.D.C. Metoda,<br>
                    Rajkot, Gujarat - 360021<br>
                    <span class="font-semibold text-slate-600 font-mono">GSTIN: 24PWWRK1234A1Z0</span>
                </div>
            </div>

            <!-- Client Info -->
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Billed To (Client)</span>
                <div class="font-bold text-slate-800">{{ $client->company_name ?? 'N/A' }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5 leading-relaxed">
                    {{ $plant->plant_name ?? 'N/A' }} Address:<br>
                    {{ $plant->shipping_address ?? 'N/A' }}<br>
                    <span class="font-semibold text-slate-600 font-mono">GSTIN: {{ $client->gst_number ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="text-right">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Invoice Details</span>
                <div class="space-y-0.5 text-[10px] text-slate-500">
                    <div>Date: <span class="font-bold text-slate-700">{{ $invoice->created_at->format('d M Y') }}</span></div>
                    <div>Due Date: <span class="font-bold text-slate-700">{{ $invoice->due_date->format('d M Y') }}</span></div>
                    <div>Payment Status: 
                        <span class="font-bold uppercase tracking-wider {{ $invoice->payment_status === 'paid' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $invoice->payment_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="mb-6">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-bold text-slate-400 uppercase">
                        <th class="py-2 pr-4">Items / SKU</th>
                        <th class="py-2 px-4 text-right">Quantity</th>
                        <th class="py-2 px-4 text-right font-mono">Unit Rate (₹)</th>
                        <th class="py-2 pl-4 text-right font-mono">Taxable Value (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($groupedItems as $item)
                        <tr>
                            <td class="py-2.5 pr-4">
                                <div class="font-bold text-slate-800">{{ $item->product_name }}</div>
                                <div class="text-[9px] text-slate-400 font-bold tracking-wider font-mono">SKU: {{ $item->sku }}</div>
                            </td>
                            <td class="py-2.5 px-4 text-right text-slate-600 font-semibold">{{ $item->quantity }} units</td>
                            <td class="py-2.5 px-4 text-right text-slate-600 font-mono">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 pl-4 text-right font-bold text-slate-800 font-mono">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary & Totals -->
        <div class="grid grid-cols-2 gap-6 pt-4 border-t border-slate-100 mb-6">
            <!-- Bank Details (Left side) -->
            <div class="text-[10px] text-slate-500 leading-normal bg-slate-50/50 p-3 rounded-lg print:bg-white print:border print:border-slate-200">
                <span class="block font-bold text-slate-700 uppercase tracking-wider mb-1 text-[9px]">Settlement Bank Accounts</span>
                <table class="w-full text-left text-[9px] leading-tight">
                    <tr>
                        <td class="font-bold text-slate-600 pr-1">Bank Name:</td>
                        <td>State Bank of India (SBI)</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-slate-600 pr-1">Account:</td>
                        <td class="font-semibold">Praful Welding Works</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-slate-600 pr-1">A/C No:</td>
                        <td class="font-mono">33445566778</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-slate-600 pr-1">IFSC:</td>
                        <td class="font-mono">SBIN0001234</td>
                    </tr>
                </table>
            </div>

            <!-- Tax Computation breakdown (Right side) -->
            <div class="flex flex-col space-y-1.5 text-xs items-end">
                <div class="flex justify-between w-full max-w-xs text-slate-500 text-[11px]">
                    <span>Taxable Subtotal:</span>
                    <span class="font-bold font-mono">₹{{ number_format($invoice->total_taxable_value, 2) }}</span>
                </div>
                
                @if ($invoice->cgst > 0)
                    <div class="flex justify-between w-full max-w-xs text-slate-500 text-[11px]">
                        <span>CGST (9.0%):</span>
                        <span class="font-bold font-mono">₹{{ number_format($invoice->cgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between w-full max-w-xs text-slate-500 text-[11px]">
                        <span>SGST (9.0%):</span>
                        <span class="font-bold font-mono">₹{{ number_format($invoice->sgst, 2) }}</span>
                    </div>
                @else
                    <div class="flex justify-between w-full max-w-xs text-slate-500 text-[11px]">
                        <span>IGST (18.0%):</span>
                        <span class="font-bold font-mono">₹{{ number_format($invoice->igst, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between w-full max-w-xs border-t border-slate-100 pt-2 text-sm font-black text-slate-800">
                    <span>Total Amount:</span>
                    <span class="font-mono text-base text-slate-900">₹{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Bottom Terms & Signatures -->
        <div class="flex flex-row justify-between items-end gap-6 text-[10px] text-slate-400 mt-8 pt-4 border-t border-slate-100">
            <div>
                <span class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[8px]">Terms & Conditions</span>
                <ul class="list-disc list-inside space-y-0.5 text-[9px] leading-tight">
                    <li>All disputes subject to Rajkot jurisdiction.</li>
                    <li>Interest @18% p.a. charged on overdue invoices.</li>
                </ul>
            </div>
            
            <div class="text-right flex flex-col items-end">
                <div class="text-[8px] font-bold text-slate-450 uppercase tracking-widest">Authorized Signatory</div>
                <div class="h-8 w-24 border-b border-slate-300 mt-1"></div>
                <div class="mt-0.5 text-[8px] text-slate-400">Praful Welding Works</div>
            </div>
        </div>

    </div>

    <!-- Auto triggering browser print -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
