@extends('layouts.app')

@section('title', 'Employees Directory')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Employees Directory</h1>
        <p class="text-sm text-slate-500">Manage and catalog welder personnel profiles, salary configurations, and fabrication rates.</p>
    </div>

    <!-- 1. INSERT FORM AT THE TOP -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Add New Employee
        </h3>
        <form action="{{ route('employees.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                    <input type="text" name="full_name" placeholder="Employee full name..." required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wage Configuration Type</label>
                    <select name="wage_type" id="wageTypeSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="piece-rate">Piece-rate (Accrues by units logged)</option>
                        <option value="fixed">Fixed Salary (Monthly regular payout)</option>
                    </select>
                </div>
            </div>

            <div id="pieceRateField" class="space-y-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Piece Rate Per Manufactured Rack (₹)</label>
                <input type="number" name="piece_rate_per_unit" step="0.01" min="0" placeholder="e.g. 45.00" value="45.00"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
            </div>

            <div id="fixedSalaryField" class="hidden space-y-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Monthly Basic Fixed Salary (₹)</label>
                <input type="number" name="monthly_salary" step="0.01" min="0" placeholder="e.g. 20000.00"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
            </div>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                Register Employee Profile
            </button>
        </form>
    </div>

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Employees Directory Ledger
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Employee Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Wage Type</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Salary Rate Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($staffProfiles as $staff)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $staff->full_name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $staff->wage_type === 'piece-rate' ? 'bg-indigo-50 border border-indigo-200 text-indigo-700' : 'bg-slate-100 border border-slate-200 text-slate-700' }}">
                                    {{ $staff->wage_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-700">
                                @if ($staff->wage_type === 'piece-rate')
                                    ₹{{ number_format($staff->piece_rate_per_unit, 2) }} / unit rack
                                @else
                                    ₹{{ number_format($staff->monthly_salary, 2) }} / month basic
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="mt-4">
            {{ $staffProfiles->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
    const wageTypeSelect = document.getElementById('wageTypeSelect');
    const pieceRateField = document.getElementById('pieceRateField');
    const fixedSalaryField = document.getElementById('fixedSalaryField');
    
    wageTypeSelect.addEventListener('change', function() {
        if (this.value === 'piece-rate') {
            pieceRateField.classList.remove('hidden');
            fixedSalaryField.classList.add('hidden');
            fixedSalaryField.querySelector('input').value = '';
        } else {
            pieceRateField.classList.add('hidden');
            fixedSalaryField.classList.remove('hidden');
            pieceRateField.querySelector('input').value = '';
        }
    });
</script>
@endsection
