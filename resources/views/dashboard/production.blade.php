@extends('layouts.app')

@section('title', 'Production Logs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Production Logs</h1>
        <p class="text-sm text-slate-500">Record rack manufacturing batches and compile staff piece-rate work outputs.</p>
    </div>

    <!-- 1. INSERT FORM AT THE TOP -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Log Finished Rack Output
        </h3>
        <form action="{{ route('production.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty Manufactured</label>
                    <input type="number" name="quantity_manufactured" min="1" placeholder="e.g. 50" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty Rejected</label>
                    <input type="number" name="quantity_rejected" min="0" value="0" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Production Date</label>
                    <input type="date" name="production_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Recorded By</label>
                    <select name="recorded_by" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @foreach ($users->whereIn('role', ['admin', 'manager']) as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Piece-rate allocation -->
            <div class="border-t border-slate-200 pt-4">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Staff Piece-Rate Work Allocation Log</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[200px] overflow-y-auto bg-slate-50 p-4 rounded-xl border border-slate-200">
                    @foreach ($staffProfiles->where('wage_type', 'piece-rate') as $staff)
                        <div class="flex items-center justify-between text-sm bg-white p-2.5 rounded-lg border border-slate-150">
                            <span class="font-medium text-slate-700">{{ $staff->full_name }} (₹{{ $staff->piece_rate_per_unit }}/unit)</span>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="labor[{{ $staff->id }}]" min="0" placeholder="0"
                                       class="w-20 bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <span class="text-xs text-slate-400">units</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                Log Production Run
            </button>
        </form>
    </div>

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Manufacturing Logs Ledger
        </h3>
        
        @if ($productionLogs->isEmpty())
            <div class="text-center text-slate-400 py-10">No production logs recorded yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Production Date</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Finished Good Product</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Qty Manufactured</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Qty Rejected</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($productionLogs as $log)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $log->production_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $log->finishedGood->product_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $log->quantity_manufactured }} units</td>
                                <td class="px-6 py-4 text-right text-rose-600 font-semibold">{{ $log->quantity_rejected }} units</td>
                                <td class="px-6 py-4 text-slate-600">{{ $log->recordedByUser->name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $productionLogs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
