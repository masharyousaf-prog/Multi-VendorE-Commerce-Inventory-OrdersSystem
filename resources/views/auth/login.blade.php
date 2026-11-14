@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="theme-card rounded-2xl p-8 md:p-10 w-full max-w-md space-y-6 shadow-lg">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 mx-auto flex items-center justify-center text-white text-2xl font-extrabold">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <h1 class="text-2xl font-extrabold">Sign In to Your Account</h1>
            <p class="text-xs theme-muted">Access your orders, wishlist, or seller dashboard</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold block mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="you@example.com"
                       class="w-full px-4 py-3 rounded-xl text-xs focus:outline-none">
            </div>

            <div>
                <label class="text-xs font-bold block mb-1.5">Password</label>
                <input type="password" name="password" required
                       placeholder="Enter your password"
                       class="w-full px-4 py-3 rounded-xl text-xs focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs transition">
                Sign In <i class="fa-solid fa-arrow-right ml-1"></i>
            </button>
        </form>

        <div class="text-center text-xs theme-muted pt-3 border-t" style="border-color: var(--border-color);">
            New to NexaMart? <a href="{{ route('register') }}" class="text-indigo-500 font-extrabold hover:underline">Create an Account</a>
        </div>
    </div>
</div>
@endsection
