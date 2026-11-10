@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div data-tilt class="card-3d glass-panel rounded-3xl p-8 md:p-10 w-full max-w-md space-y-6 border-indigo-500/30 shadow-2xl">
        <div class="text-center space-y-2 card-3d-floating">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-orange-500 mx-auto flex items-center justify-center text-white text-2xl font-extrabold shadow-lg">
                <i class="fa-solid fa-key"></i>
            </div>
            <h1 class="text-2xl font-extrabold">Sign In to NexaMart</h1>
            <p class="text-xs opacity-70">Secured by Stateless HS256 JWT Authentication</p>
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold block mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="you@example.com"
                       class="w-full px-4 py-3 rounded-2xl text-xs glass-panel focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="text-xs font-bold block mb-1.5">Password</label>
                <input type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-2xl text-xs glass-panel focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-orange-500 text-white font-extrabold text-xs shadow-lg hover:opacity-95 transition">
                Authenticate & Generate JWT <i class="fa-solid fa-arrow-right ml-1"></i>
            </button>
        </form>

        <div class="text-center text-xs opacity-75 pt-2 border-t border-slate-500/20">
            New to NexaMart? <a href="{{ route('register') }}" class="text-indigo-400 font-extrabold hover:underline">Create an Instant-Approved Account</a>
        </div>
    </div>
</div>
@endsection
