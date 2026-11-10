@extends('layouts.app')

@section('content')
@php
    $subtotal = $cart->items->sum(fn($i) => $i->product->price * $i->quantity);
    $freeShippingThreshold = 150;
    $progress = min(100, round(($subtotal / $freeShippingThreshold) * 100));
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-extrabold"><i class="fa-solid fa-bag-shopping text-indigo-500 mr-2"></i>Your Shopping Bag</h1>
        <a href="{{ route('products.index') }}" class="text-xs font-bold text-indigo-400 hover:underline">
            <i class="fa-solid fa-arrow-left mr-1"></i> Continue Shopping
        </a>
    </div>

    <!-- Free Shipping Progress Banner -->
    <div class="glass-panel rounded-2xl p-4 space-y-2 border-indigo-500/30">
        <div class="flex items-center justify-between text-xs font-bold">
            @if($subtotal >= $freeShippingThreshold)
                <span class="text-emerald-400"><i class="fa-solid fa-truck-fast mr-1"></i> Congratulations! You unlocked FREE Global Express Shipping!</span>
            @else
                <span>Add <strong class="text-orange-400">${{ number_format($freeShippingThreshold - $subtotal, 2) }}</strong> more to unlock <strong>FREE Global Express Shipping</strong></span>
            @endif
            <span>{{ $progress }}%</span>
        </div>
        <div class="w-full h-2 rounded-full bg-slate-700/30 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-400 transition-all duration-500" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    @if($cart->items->count())
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Cart Items List -->
            <div class="lg:col-span-8 space-y-4">
                @foreach($cart->items as $item)
                    <div class="glass-panel rounded-3xl p-5 flex flex-wrap sm:flex-nowrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 rounded-2xl bg-slate-800/30 overflow-hidden flex items-center justify-center shrink-0">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-cube text-2xl text-indigo-400"></i>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase text-indigo-400">
                                    <i class="fa-solid fa-store mr-1"></i>{{ $item->product->vendor->store_name ?? $item->product->vendor->name ?? 'Official Vendor' }}
                                </span>
                                <a href="{{ route('products.show', $item->product) }}" class="block font-extrabold text-base hover:text-indigo-400">
                                    {{ $item->product->name }}
                                </a>
                                <div class="text-xs opacity-70 mt-0.5">${{ number_format($item->product->price, 2) }} each</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 ml-auto">
                            <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}"
                                       class="w-16 px-2 py-1.5 rounded-xl text-center text-xs font-bold glass-panel">
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-indigo-500/20 text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-bold transition">
                                    Update
                                </button>
                            </form>

                            <div class="text-right min-w-[80px]">
                                <div class="font-extrabold text-base text-indigo-400">${{ number_format($item->product->price * $item->quantity, 2) }}</div>
                            </div>

                            <form method="POST" action="{{ route('cart.remove', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2.5 rounded-xl bg-rose-500/15 text-rose-400 hover:bg-rose-600 hover:text-white text-xs transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sticky Order Summary -->
            <div class="lg:col-span-4">
                <div class="glass-panel rounded-3xl p-6 space-y-4 sticky top-24">
                    <h3 class="font-extrabold text-lg border-b border-slate-500/20 pb-3">Order Summary</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="opacity-70">Items Subtotal ({{ $cart->items->sum('quantity') }} units)</span>
                            <span class="font-bold">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70">Estimated Shipping</span>
                            <span class="font-bold text-emerald-400">{{ $subtotal >= $freeShippingThreshold ? 'FREE' : '$12.00' }}</span>
                        </div>
                    </div>
                    <div class="border-t border-slate-500/20 pt-3 flex justify-between items-baseline">
                        <span class="font-extrabold text-sm">Estimated Total</span>
                        <span class="text-2xl font-extrabold text-indigo-400">${{ number_format($subtotal, 2) }}</span>
                    </div>

                    <form method="POST" action="{{ route('cart.checkout') }}">
                        @csrf
                        <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-orange-500 text-white font-extrabold text-xs shadow-lg hover:opacity-95 transition">
                            Proceed to Express Checkout <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="glass-panel rounded-3xl p-12 text-center space-y-4">
            <i class="fa-solid fa-cart-arrow-down text-5xl text-indigo-400"></i>
            <h3 class="text-xl font-extrabold">Your shopping cart is empty</h3>
            <p class="text-xs opacity-70">Explore our daily lightning deals and 3D product catalogue.</p>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 rounded-2xl bg-indigo-600 text-white text-xs font-extrabold">Discover Products</a>
        </div>
    @endif
</div>
@endsection
