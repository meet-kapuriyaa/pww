@extends('layouts.app')

@section('title', 'Profile Information')

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold text-slate-800">Profile Information</h1>
            <a href="{{ route('overview') }}" class="flex items-center space-x-1.5 bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Panel</span>
            </a>
        </div>
    </div>

    <!-- Forms Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Form 1: Profile Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Profile Information</h2>
                <p class="text-xs text-slate-400 mt-1 mb-6">Update your account's profile information and email address.</p>
                
                <form action="{{ route('profile.update') }}" method="POST" class="ajax-form space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition duration-150 text-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form 2: Update Password -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Update Password</h2>
                <p class="text-xs text-slate-400 mt-1 mb-6">Ensure your account is using a long, random password to stay secure.</p>
                
                <form action="{{ route('profile.password') }}" method="POST" class="ajax-form space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">New Password</label>
                        <input type="password" name="new_password" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Confirm Password</label>
                        <input type="password" name="new_password_confirmation" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition duration-150 text-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
