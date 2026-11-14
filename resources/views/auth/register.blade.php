@extends('layouts.app')

@section('content')
<div class="min-h-[82vh] flex items-center justify-center px-4 py-12">
    <div class="theme-card rounded-2xl p-8 md:p-10 w-full max-w-lg space-y-6 shadow-lg"
         x-data="{ role: '{{ old('role', 'customer') }}' }">

        <div class="text-center space-y-2">
            <span class="px-3 py-1 rounded text-[10px] font-extrabold bg-emerald-600 text-white uppercase">
                Global Marketplace Registration
            </span>
            <h1 class="text-2xl font-extrabold mt-2">Create Your NexaMart Account</h1>
            <p class="text-xs theme-muted">Shop thousands of verified products or launch your own storefront</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="role" :value="role">

            <!-- Role Selector Cards -->
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="role = 'customer'"
                        :class="role === 'customer' ? 'ring-2 ring-indigo-600' : 'opacity-75'"
                        class="p-4 rounded-xl theme-elevated text-left transition">
                    <i class="fa-solid fa-bag-shopping text-indigo-500 text-lg mb-1"></i>
                    <div class="font-extrabold text-xs">Customer Account</div>
                    <div class="text-[10px] theme-muted">Browse, review & order</div>
                </button>
                <button type="button" @click="role = 'vendor'"
                        :class="role === 'vendor' ? 'ring-2 ring-orange-600' : 'opacity-75'"
                        class="p-4 rounded-xl theme-elevated text-left transition">
                    <i class="fa-solid fa-store text-orange-500 text-lg mb-1"></i>
                    <div class="font-extrabold text-xs">Seller / Vendor</div>
                    <div class="text-[10px] theme-muted">Open a vendor storefront</div>
                </button>
            </div>

            <div>
                <label class="text-xs font-bold block mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Enter your full name" class="w-full px-4 py-3 rounded-xl text-xs">
            </div>

            <div x-show="role === 'vendor'" x-transition>
                <label class="text-xs font-bold block mb-1 text-orange-500">Official Storefront Name</label>
                <input type="text" name="store_name" value="{{ old('store_name') }}" placeholder="e.g. Apex Tech Global Store" class="w-full px-4 py-3 rounded-xl text-xs">
            </div>

            <div>
                <label class="text-xs font-bold block mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com" class="w-full px-4 py-3 rounded-xl text-xs">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold block mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Min 8 characters" class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password" class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs transition">
                Create Account <i class="fa-solid fa-arrow-right ml-1"></i>
            </button>
        </form>

        <div class="text-center text-xs theme-muted pt-3 border-t" style="border-color: var(--border-color);">
            Already have an account? <a href="{{ route('login') }}" class="text-indigo-500 font-extrabold hover:underline">Sign In</a>
        </div>
    </div>
</div>
@endsection
