@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    @if(isset($completedOrder))
        <!-- DYNAMIC 5-STEP ORDER TRACKING RECEIPT -->
        <div class="theme-card rounded-2xl p-8 space-y-8 border-2 border-emerald-600">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-6" style="border-color: var(--border-color);">
                <div>
                    <span class="px-3 py-1 rounded text-xs font-extrabold bg-emerald-600 text-white uppercase">
                        <i class="fa-solid fa-check-double mr-1"></i> Order Status: {{ strtoupper($completedOrder->status) }}
                    </span>
                    <h1 class="text-3xl font-extrabold mt-2">Order Placed Successfully!</h1>
                    <p class="text-xs theme-muted mt-1">
                        Order ID: <strong>#{{ $completedOrder->id }}</strong> |
                        Tracking ID: <strong class="text-indigo-500 font-mono text-sm">{{ $completedOrder->tracking_number }}</strong>
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-xs theme-muted">Cash on Delivery Amount</div>
                    <div class="text-3xl font-extrabold text-emerald-500">${{ number_format($completedOrder->total_price, 2) }}</div>
                    @if($completedOrder->discount_amount > 0)
                        <div class="text-[11px] text-orange-500 font-bold">Saved ${{ number_format($completedOrder->discount_amount, 2) }} ({{ $completedOrder->coupon_code }})</div>
                    @endif
                </div>
            </div>

            <!-- Dynamic 5-Step Progress Tracker Backed by Database Status -->
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                @foreach($completedOrder->tracking_steps as $step)
                    <div class="p-4 rounded-xl theme-elevated {{ $step['completed'] ? 'border-2 border-emerald-600' : 'opacity-50' }} flex flex-col items-center text-center">
                        <div class="w-11 h-11 rounded-xl {{ $step['completed'] ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300' }} flex items-center justify-center text-lg mb-2">
                            <i class="fa-solid {{ $step['icon'] }}"></i>
                        </div>
                        <div class="font-extrabold text-xs">{{ $step['title'] }}</div>
                        <div class="text-[10px] theme-muted mt-0.5">{{ $step['desc'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap justify-between items-center gap-4 pt-4 border-t" style="border-color: var(--border-color);">
                <div class="text-xs theme-muted">
                    <i class="fa-solid fa-location-dot text-indigo-500 mr-1"></i>
                    Delivering to: <strong>{{ $completedOrder->shipping_name }}</strong> ({{ $completedOrder->shipping_phone }}), {{ $completedOrder->shipping_address }}, {{ $completedOrder->shipping_city }}
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('orders.track', ['tracking_number' => $completedOrder->tracking_number]) }}" class="px-5 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-extrabold transition">
                        <i class="fa-solid fa-truck-fast mr-1"></i> Live Track Order
                    </a>
                    <a href="{{ route('orders.my') }}" class="px-5 py-3 rounded-xl theme-elevated text-xs font-extrabold">
                        View My Orders
                    </a>
                    <a href="{{ route('products.index') }}" class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- COD CHECKOUT FORM -->
        @php
            $subtotal = $cart->items->sum(fn($i) => $i->product->price * $i->quantity);
        @endphp
        <form method="POST" action="{{ route('cart.checkout') }}"
              x-data="{
                subtotal: {{ (float)$subtotal }},
                coupon: '',
                discount: 0,
                couponMsg: '',
                applyCoupon() {
                    if (this.coupon.trim().toUpperCase() === 'MEGA20' && this.subtotal >= 50) {
                        this.discount = +(this.subtotal * 0.20).toFixed(2);
                        this.couponMsg = '20% OFF MEGA20 Promo Applied!';
                    } else if (this.coupon.trim() !== '') {
                        this.discount = 0;
                        this.couponMsg = 'Coupon code will be verified on order placement.';
                    }
                }
              }"
              class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            @csrf
            <input type="hidden" name="confirm_order" value="1">
            <input type="hidden" name="payment_method" value="cod">

            <div class="lg:col-span-7 space-y-6">
                <div class="theme-card rounded-2xl p-6 space-y-4">
                    <h2 class="text-lg font-extrabold"><i class="fa-solid fa-truck-ramp-box text-indigo-500 mr-2"></i>1. Delivery Destination</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold block mb-1">Recipient Full Name</label>
                            <input type="text" name="shipping_name" required value="{{ old('shipping_name', auth()->user()->name) }}" placeholder="Full Recipient Name" class="w-full px-4 py-3 rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Phone / Mobile Number</label>
                            <input type="text" name="shipping_phone" required value="{{ old('shipping_phone') }}" placeholder="e.g. +92 300 1234567" class="w-full px-4 py-3 rounded-xl text-xs">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-bold block mb-1">Complete Street Address</label>
                            <input type="text" name="shipping_address" required value="{{ old('shipping_address') }}" placeholder="House / Flat No., Street, Sector / Area" class="w-full px-4 py-3 rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">City</label>
                            <input type="text" name="shipping_city" required value="{{ old('shipping_city') }}" placeholder="e.g. Islamabad, Lahore, Karachi" class="w-full px-4 py-3 rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Postal / ZIP Code</label>
                            <input type="text" name="shipping_postal_code" required value="{{ old('shipping_postal_code') }}" placeholder="e.g. 44000" class="w-full px-4 py-3 rounded-xl text-xs">
                        </div>
                    </div>
                </div>

                <!-- Strictly Cash on Delivery (COD) Option -->
                <div class="theme-card rounded-2xl p-6 space-y-3">
                    <h2 class="text-lg font-extrabold"><i class="fa-solid fa-money-bill-wave text-emerald-500 mr-2"></i>2. Payment Method</h2>
                    <div class="p-4 rounded-xl theme-elevated border-2 border-emerald-600 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold">Cash on Delivery (COD)</div>
                                <div class="text-xs theme-muted">Pay in cash at your doorstep when the courier delivers your package.</div>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded bg-emerald-600 text-white text-[10px] font-extrabold uppercase">Selected</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="theme-card rounded-2xl p-6 space-y-5 sticky top-24">
                    <h3 class="text-lg font-extrabold">Order Summary</h3>

                    <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                        @foreach($cart->items as $item)
                            <div class="flex items-center justify-between text-xs py-1.5 border-b" style="border-color: var(--border-color);">
                                <span class="truncate max-w-[200px] font-semibold">{{ $item->quantity }}x {{ $item->product->name }}</span>
                                <span class="font-bold">${{ number_format($item->product->price * $item->quantity, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold theme-muted">Promo / Voucher Code (Try <code class="text-orange-500">MEGA20</code>)</label>
                        <div class="flex gap-2">
                            <input type="text" name="coupon_code" x-model="coupon" placeholder="Enter code..." class="flex-1 px-3.5 py-2.5 rounded-xl text-xs font-mono uppercase">
                            <button type="button" @click="applyCoupon()" class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-extrabold">Apply</button>
                        </div>
                        <p x-show="couponMsg" x-text="couponMsg" class="text-[11px] font-bold text-emerald-500"></p>
                    </div>

                    <div class="space-y-2 text-xs border-t pt-4" style="border-color: var(--border-color);">
                        <div class="flex justify-between">
                            <span class="theme-muted">Subtotal</span>
                            <span class="font-bold" x-text="'$' + subtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-emerald-500" x-show="discount > 0">
                            <span>Promo Discount</span>
                            <span class="font-extrabold" x-text="'-$' + discount.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold pt-2 border-t" style="border-color: var(--border-color);">
                            <span>Total Payable on Delivery (COD)</span>
                            <span class="text-indigo-500" x-text="'$' + (subtotal - discount).toFixed(2)"></span>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm transition">
                        <i class="fa-solid fa-circle-check mr-1.5"></i> Confirm Cash on Delivery Order
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
