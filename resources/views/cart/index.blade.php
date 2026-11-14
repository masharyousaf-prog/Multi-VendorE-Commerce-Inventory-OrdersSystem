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
        <a href="{{ route('products.index') }}" class="text-xs font-bold text-indigo-500 hover:underline">
            <i class="fa-solid fa-arrow-left mr-1"></i> Continue Shopping
        </a>
    </div>

    <div class="theme-card rounded-2xl p-4 space-y-2">
        <div class="flex items-center justify-between text-xs font-bold">
            @if($subtotal >= $freeShippingThreshold)
                <span class="text-emerald-500"><i class="fa-solid fa-truck-fast mr-1"></i> Congratulations! You unlocked FREE Global Express Shipping!</span>
            @else
                <span>Add <strong class="text-orange-500">${{ number_format($freeShippingThreshold - $subtotal, 2) }}</strong> more to unlock <strong>FREE Global Express Shipping</strong></span>
            @endif
            <span>{{ $progress }}%</span>
        </div>
        <div class="w-full h-2 rounded-full bg-slate-500/20 overflow-hidden">
            <div class="h-full bg-emerald-600 transition-all duration-500" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    @if($cart->items->count())
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8 space-y-4">
                @foreach($cart->items as $item)
                    <div class="theme-card rounded-2xl p-5 flex flex-wrap sm:flex-nowrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 rounded-xl bg-slate-800/10 overflow-hidden flex items-center justify-center shrink-0">
                                @if($item->product->image_url)
                                    <img src="{{ $item->product->image_url }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-box text-2xl text-indigo-500"></i>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase text-indigo-500">
                                    <i class="fa-solid fa-store mr-1"></i>{{ $item->product->vendor->store_name ?? $item->product->vendor->name ?? 'Official Vendor' }}
                                </span>
                                <a href="{{ route('products.show', $item->product) }}" class="block font-extrabold text-base hover:text-indigo-500">
                                    {{ $item->product->name }}
                                </a>
                                <div class="text-xs theme-muted mt-0.5">${{ number_format($item->product->price, 2) }} each</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 ml-auto">
                            <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}"
                                       class="w-16 px-2 py-1.5 rounded-xl text-center text-xs font-bold">
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                                    Update
                                </button>
                            </form>

                            <div class="text-right min-w-[80px]">
                                <div class="font-extrabold text-base text-indigo-500">${{ number_format($item->product->price * $item->quantity, 2) }}</div>
                            </div>

                            <form method="POST" action="{{ route('cart.remove', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="lg:col-span-4">
                <div class="theme-card rounded-2xl p-6 space-y-4 sticky top-24">
                    <h3 class="font-extrabold text-lg border-b pb-3" style="border-color: var(--border-color);">Order Summary</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="theme-muted">Items Subtotal ({{ $cart->items->sum('quantity') }} units)</span>
                            <span class="font-bold">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="theme-muted">Estimated Shipping</span>
                            <span class="font-bold text-emerald-500">{{ $subtotal >= $freeShippingThreshold ? 'FREE' : '$12.00' }}</span>
                        </div>
                    </div>
                    <div class="border-t pt-3 flex justify-between items-baseline" style="border-color: var(--border-color);">
                        <span class="font-extrabold text-sm">Estimated Total</span>
                        <span class="text-2xl font-extrabold text-indigo-500">${{ number_format($subtotal, 2) }}</span>
                    </div>

                    <form method="POST" action="{{ route('cart.checkout') }}">
                        @csrf
                        <button type="submit" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs transition">
                            Proceed to Express Checkout <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="theme-card rounded-2xl p-12 text-center space-y-4">
            <i class="fa-solid fa-cart-arrow-down text-5xl text-indigo-500"></i>
            <h3 class="text-xl font-extrabold">Your shopping cart is empty</h3>
            <p class="text-xs theme-muted">Explore our daily lightning deals and product catalogue.</p>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 rounded-xl bg-indigo-600 text-white text-xs font-extrabold">Discover Products</a>
        </div>
    @endif
</div>
@endsection
