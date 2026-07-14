@extends('layouts.app')

@section('title', 'Clients & Plants')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Clients & Plants</h1>
        <p class="text-sm text-slate-500">Manage B2B corporate profiles and individual factory delivery destinations.</p>
    </div>

    <!-- 1. INSERT FORMS AT THE TOP (2-Column Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form 1: Client Profile -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Add Corporate Client Profile
                </h3>
                <form action="{{ route('clients.store') }}" method="POST" class="ajax-form space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Company / Corporate Name</label>
                        <input type="text" name="company_name" placeholder="e.g. Balaji Wafers Pvt. Ltd." required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GSTIN Registration Number</label>
                        <input type="text" name="gst_number" placeholder="e.g. 24AAAAB1111A1Z1" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Corporate Headquarters Address</label>
                        <textarea name="corporate_address" rows="2" placeholder="Full corporate address..." required
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                        Create Client Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Form 2: Register Plant Address -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Register B2B Delivery Plant Address
                </h3>
                <form action="{{ route('clients.plants.store') }}" method="POST" class="ajax-form space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Corporate Client Profile</label>
                            <select name="client_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select client...</option>
                                @foreach ($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Plant State (GST Tax region)</label>
                            <select name="state" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="Gujarat">Gujarat (Intrastate)</option>
                                <option value="Madhya Pradesh">Madhya Pradesh (Interstate)</option>
                                <option value="Maharashtra">Maharashtra (Interstate)</option>
                                <option value="Rajasthan">Rajasthan (Interstate)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Plant Location Name</label>
                        <input type="text" name="plant_name" placeholder="e.g. Rajkot Factory, Valsad Plant" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shipping Warehouse Address</label>
                        <textarea name="shipping_address" rows="2" placeholder="Full plant shipping address..." required
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition duration-150 text-sm">
                        Register Plant Address
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2.5M9 21h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Corporate Clients Directory
        </h3>
        
        <div class="space-y-6">
            @if ($clients->isEmpty())
                <div class="text-center text-slate-400 py-10">No client profiles registered yet.</div>
            @else
                @foreach ($clients as $c)
                    <div class="border border-slate-200 rounded-2xl p-6 bg-slate-50/20 hover:bg-slate-50/50 transition">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $c->company_name }}</h3>
                                <span class="text-xs text-slate-500 font-mono">GSTIN: <span class="font-bold">{{ $c->gst_number }}</span></span>
                            </div>
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">
                                {{ $c->plants->count() }} plants registered
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1 border-r border-slate-100 pr-4">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Corporate Headquarters</span>
                                <p class="text-sm text-slate-600 mt-1 leading-relaxed">{{ $c->corporate_address }}</p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Registered Delivery Plants</span>
                                @if ($c->plants->isEmpty())
                                    <p class="text-xs text-slate-400 italic">No shipping plants registered for this client yet.</p>
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach ($c->plants as $p)
                                            <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex flex-col justify-between shadow-xs">
                                                <div>
                                                    <h4 class="font-bold text-sm text-slate-800">{{ $p->plant_name }}</h4>
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $p->shipping_address }}</p>
                                                </div>
                                                <div class="pt-2">
                                                    <span class="px-2 py-0.5 bg-blue-50 border border-blue-100 text-blue-700 text-[10px] rounded font-bold uppercase tracking-wider">{{ $p->state }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
