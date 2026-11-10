@extends('layouts.app')

@section('content')
<div class="min-h-[82vh] flex items-center justify-center px-4 py-12">
    <div class="glass-panel rounded-3xl p-8 md:p-10 w-full max-w-lg space-y-6 border-indigo-500/30 shadow-2xl"
         x-data="{ role: '{{ old('role', 'customer') }}' }">

        <div class="text-center space-y-2">
            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-400">
                <i class="fa-solid fa-bolt mr-1"></i> INSTANT ACCOUNT APPROVAL ENABLED
            </span>
            <h1 class="text-2xl font-extrabold">Create Your Global Account</h1>
            <p class="text-xs opacity-70">Start buying or selling immediately</p>
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-semibold space-y-1">
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="role" :value="role">

            <!-- Interactive Role Selector Cards -->
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="role = 'customer'"
                        :class="role === 'customer' ? 'ring-2 ring-indigo-500 bg-indigo-500/15' : 'opacity-70'"
                        class="p-4 rounded-2xl glass-panel text-left transition">
                    <i class="fa-solid fa-bag-shopping text-indigo-400 text-lg mb-1"></i>
                    <div class="font-extrabold text-xs">Shopper Account</div>
                    <div class="text-[10px] opacity-70">Browse, review & order</div>
                </button>
                <button type="button" @click="role = 'vendor'"
                        :class="role === 'vendor' ? 'ring-2 ring-orange-500 bg-orange-500/15' : 'opacity-70'"
                        class="p-4 rounded-2xl glass-panel text-left transition">
                    <i class="fa-solid fa-store text-orange-400 text-lg mb-1"></i>
                    <div class="font-extrabold text-xs">Merchant / Vendor</div>
                    <div class="text-[10px] opacity-70">Open your 3D storefront</div>
                </button>
            </div>

            <div>
                <label class="text-xs font-bold block mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="John Doe" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
            </div>

            <div x-show="role === 'vendor'" x-transition>
                <label class="text-xs font-bold block mb-1 text-orange-400">Official Storefront Name</label>
                <input type="text" name="store_name" value="{{ old('store_name') }}" placeholder="e.g. Apex Tech Global Store" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel border-orange-500/40">
            </div>

            <div>
                <label class="text-xs font-bold block mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold block mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Min 8 characters" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-orange-500 text-white font-extrabold text-xs shadow-lg hover:opacity-95 transition">
                Launch Account Instantly <i class="fa-solid fa-rocket ml-1"></i>
            </button>
        </form>
    </div>
</div>
@endsection
