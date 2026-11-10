@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    @if(isset($completedOrder))
        <!-- 5-STEP LIVE ORDER TRACKING PIPELINE (Amazon / AliExpress Style) -->
        <div class="glass-panel rounded-3xl p-8 space-y-8 border-emerald-500/40">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-500/20 pb-6">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/20 text-emerald-400">
                        <i class="fa-solid fa-check-double mr-1"></i> ORDER CONFIRMED
                    </span>
                    <h1 class="text-3xl font-extrabold mt-2">Thank You! Your Order is in Motion</h1>
                    <p class="text-xs opacity-75 mt-1">Order ID: <strong>#{{ $completedOrder->id }}</strong> | Tracking Number: <strong class="text-indigo-400 font-mono">{{ $completedOrder->tracking_number }}</strong></p>
                </div>
                <div class="text-right">
                    <div class="text-xs opacity-70">Total Charged</div>
                    <div class="text-3xl font-extrabold text-emerald-400">${{ number_format($completedOrder->total_price, 2) }}</div>
                    @if($completedOrder->discount_amount > 0)
                        <div class="text-[11px] text-orange-400 font-bold">Saved ${{ number_format($completedOrder->discount_amount, 2) }} ({{ $completedOrder->coupon_code }})</div>
                    @endif
                </div>
            </div>

            <!-- 5-Step Progress Tracker -->
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                @php
                    $steps = [
                        ['icon' => 'fa-file-invoice', 'title' => '1. Order Placed', 'desc' => 'Verified via JWT', 'active' => true],
                        ['icon' => 'fa-shield-halved', 'title' => '2. Payment Secured', 'desc' => strtoupper($completedOrder->payment_method), 'active' => true],
                        ['icon' => 'fa-boxes-packing', 'title' => '3. Vendor Packing', 'desc' => 'Warehouse notified', 'active' => true],
                        ['icon' => 'fa-truck-fast', 'title' => '4. In Transit', 'desc' => 'Express Courier', 'active' => false],
                        ['icon' => 'fa-house-circle-check', 'title' => '5. Delivered', 'desc' => 'Est. 2-3 Days', 'active' => false],
                    ];
                @endphp
                @foreach($steps as $step)
                    <div class="p-4 rounded-2xl glass-panel {{ $step['active'] ? 'border-emerald-500/50 bg-emerald-500/10' : 'opacity-50' }} flex flex-col items-center text-center">
                        <div class="w-11 h-11 rounded-2xl {{ $step['active'] ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300' }} flex items-center justify-center text-lg mb-2 shadow-lg">
                            <i class="fa-solid {{ $step['icon'] }}"></i>
                        </div>
                        <div class="font-extrabold text-xs">{{ $step['title'] }}</div>
                        <div class="text-[10px] opacity-75 mt-0.5">{{ $step['desc'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('products.index') }}" class="px-6 py-3 rounded-2xl bg-indigo-600 text-white text-xs font-extrabold">
                    Continue Shopping
                </a>
            </div>
        </div>
    @else
        <!-- INTERACTIVE EXPRESS CHECKOUT WITH LIVE COUPON VALIDATOR -->
        @php
            $subtotal = $cart->items->sum(fn($i) => $i->product->price * $i->quantity);
        @endphp
        <form method="POST" action="{{ route('cart.checkout') }}"
              x-data="{
                subtotal: {{ (float)$subtotal }},
                coupon: '',
                discount: 0,
                couponMsg: '',
                paymentMethod: 'card',
                applyCoupon() {
                    if (this.coupon.trim().toUpperCase() === 'MEGA20') {
                        this.discount = +(this.subtotal * 0.20).toFixed(2);
                        this.couponMsg = '20% OFF MEGA20 Promo Applied!';
                    } else {
                        this.discount = 0;
                        this.couponMsg = 'Coupon will be verified on submission.';
                    }
                }
              }"
              class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            @csrf
            <input type="hidden" name="confirm_order" value="1">

            <!-- Left Column: Shipping & Payment Selector -->
            <div class="lg:col-span-7 space-y-6">
                <div class="glass-panel rounded-3xl p-6 space-y-4">
                    <h2 class="text-lg font-extrabold"><i class="fa-solid fa-truck-ramp-box text-indigo-400 mr-2"></i>1. Delivery Destination</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="text" required value="{{ auth()->user()->name }}" placeholder="Full Recipient Name" class="px-4 py-3 rounded-xl text-xs glass-panel">
                        <input type="text" required placeholder="Phone / WhatsApp Number" class="px-4 py-3 rounded-xl text-xs glass-panel">
                        <input type="text" required placeholder="Street Address, House / Apartment No." class="sm:col-span-2 px-4 py-3 rounded-xl text-xs glass-panel">
                        <input type="text" required placeholder="City" class="px-4 py-3 rounded-xl text-xs glass-panel">
                        <input type="text" required placeholder="Postal / ZIP Code" class="px-4 py-3 rounded-xl text-xs glass-panel">
                    </div>
                </div>

                <div class="glass-panel rounded-3xl p-6 space-y-4">
                    <h2 class="text-lg font-extrabold"><i class="fa-solid fa-credit-card text-orange-400 mr-2"></i>2. Payment Method</h2>
                    <input type="hidden" name="payment_method" :value="paymentMethod">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <button type="button" @click="paymentMethod = 'card'"
                                :class="paymentMethod === 'card' ? 'ring-2 ring-indigo-500 bg-indigo-500/15' : ''"
                                class="p-4 rounded-2xl glass-panel text-left space-y-1">
                            <i class="fa-brands fa-cc-visa text-xl text-indigo-400"></i>
                            <div class="text-xs font-extrabold">Credit / Debit Card</div>
                            <div class="text-[10px] opacity-65">Instant 3D Secure</div>
                        </button>
                        <button type="button" @click="paymentMethod = 'cod'"
                                :class="paymentMethod === 'cod' ? 'ring-2 ring-emerald-500 bg-emerald-500/15' : ''"
                                class="p-4 rounded-2xl glass-panel text-left space-y-1">
                            <i class="fa-solid fa-money-bill-wave text-xl text-emerald-400"></i>
                            <div class="text-xs font-extrabold">Cash on Delivery</div>
                            <div class="text-[10px] opacity-65">Pay at your doorstep</div>
                        </button>
                        <button type="button" @click="paymentMethod = 'wallet'"
                                :class="paymentMethod === 'wallet' ? 'ring-2 ring-orange-500 bg-orange-500/15' : ''"
                                class="p-4 rounded-2xl glass-panel text-left space-y-1">
                            <i class="fa-solid fa-wallet text-xl text-orange-400"></i>
                            <div class="text-xs font-extrabold">Digital Escrow</div>
                            <div class="text-[10px] opacity-65">Zero-fee transfer</div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Promo Code & Final Order Confirmation -->
            <div class="lg:col-span-5">
                <div class="glass-panel rounded-3xl p-6 space-y-5 sticky top-24">
                    <h3 class="text-lg font-extrabold">Order Review</h3>

                    <!-- Promo Voucher Input -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold opacity-75">Promo / Voucher Code (Try <code class="text-orange-400">MEGA20</code>)</label>
                        <div class="flex gap-2">
                            <input type="text" name="coupon_code" x-model="coupon" placeholder="Enter code..." class="flex-1 px-3.5 py-2.5 rounded-xl text-xs font-mono uppercase glass-panel">
                            <button type="button" @click="applyCoupon()" class="px-4 py-2.5 rounded-xl bg-orange-500 text-white text-xs font-extrabold">Apply</button>
                        </div>
                        <p x-show="couponMsg" x-text="couponMsg" class="text-[11px] font-bold text-emerald-400"></p>
                    </div>

                    <div class="space-y-2 text-xs border-t border-slate-500/20 pt-4">
                        <div class="flex justify-between">
                            <span class="opacity-70">Subtotal</span>
                            <span class="font-bold" x-text="'$' + subtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-emerald-400" x-show="discount > 0">
                            <span>Promo Discount</span>
                            <span class="font-extrabold" x-text="'-$' + discount.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold pt-2 border-t border-slate-500/20">
                            <span>Total Payable</span>
                            <span class="text-indigo-400" x-text="'$' + (subtotal - discount).toFixed(2)"></span>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-extrabold text-sm shadow-xl hover:scale-[1.01] transition">
                        <i class="fa-solid fa-shield-check mr-1.5"></i> Complete JWT-Verified Order
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
