@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="text-center space-y-2">
        <span class="text-xs font-bold uppercase tracking-widest text-indigo-500">Real-Time Logistics Telemetry</span>
        <h1 class="text-3xl font-extrabold"><i class="fa-solid fa-magnifying-glass-location text-indigo-500 mr-2"></i>Track Your Order</h1>
        <p class="text-xs theme-muted">Enter the Tracking ID provided at checkout (e.g., <code class="font-mono text-indigo-500">NXM-...</code>) to inspect live fulfillment status.</p>
    </div>

    <!-- Tracking ID Search Form -->
    <form method="GET" action="{{ route('orders.track') }}" class="theme-card rounded-2xl p-6 flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
        <input type="text"
               name="tracking_number"
               value="{{ $trackingNumber }}"
               required
               placeholder="Enter Tracking ID (e.g. NXM-ABCD-100001) or Order ID..."
               class="flex-1 px-4 py-3 rounded-xl text-sm font-mono uppercase">
        <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold transition">
            <i class="fa-solid fa-satellite-dish mr-1.5"></i> Track Package
        </button>
    </form>

    @if($trackingNumber !== '' && !$order)
        <div class="theme-card rounded-2xl p-10 text-center space-y-3 border-2 border-rose-600">
            <i class="fa-solid fa-circle-exclamation text-4xl text-rose-500"></i>
            <h3 class="text-lg font-extrabold">No Order Found for "{{ $trackingNumber }}"</h3>
            <p class="text-xs theme-muted">Please double-check your tracking number or visit your <a href="{{ route('orders.my') }}" class="text-indigo-500 underline font-bold">Order History</a>.</p>
        </div>
    @endif

    @if($order)
        <div class="theme-card rounded-2xl p-6 md:p-8 space-y-8 border-2 {{ $order->status === 'cancelled' ? 'border-rose-600' : 'border-indigo-600' }}">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-5" style="border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="px-3 py-1 rounded text-xs font-extrabold uppercase {{ $order->status === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-indigo-600 text-white' }}">
                            {{ $order->status }}
                        </span>
                        <span class="font-mono text-sm font-extrabold text-orange-500">{{ $order->tracking_number }}</span>
                    </div>
                    <h2 class="text-xl font-extrabold mt-2">Order #{{ $order->id }} — Placed {{ $order->created_at->format('M d, Y h:i A') }}</h2>
                    @if($order->shipping_address)
                        <p class="text-xs theme-muted mt-1">
                            <i class="fa-solid fa-location-dot text-indigo-500 mr-1"></i>
                            Recipient: <strong>{{ $order->shipping_name }}</strong> ({{ $order->shipping_phone }}) — {{ $order->shipping_address }}, {{ $order->shipping_city }} {{ $order->shipping_postal_code }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-xs theme-muted">Payment Method: <strong class="uppercase">{{ $order->payment_method }}</strong></div>
                    <div class="text-2xl font-extrabold text-emerald-500 mt-0.5">${{ number_format($order->total_price, 2) }}</div>
                </div>
            </div>

            @if($order->status === 'cancelled')
                <div class="p-5 rounded-xl bg-rose-600 text-white text-sm font-bold flex items-center gap-3">
                    <i class="fa-solid fa-ban text-2xl"></i>
                    <div>This order was cancelled and reserved inventory has been returned to stock.</div>
                </div>
            @else
                <!-- Dynamic 5-Step Progress Pipeline -->
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                    @foreach($order->tracking_steps as $step)
                        <div class="p-4 rounded-xl theme-elevated {{ $step['current'] ? 'ring-2 ring-orange-500' : '' }} {{ $step['completed'] ? 'border-2 border-emerald-600' : 'opacity-45' }} flex flex-col items-center text-center">
                            <div class="w-11 h-11 rounded-xl {{ $step['completed'] ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300' }} flex items-center justify-center text-lg mb-2">
                                <i class="fa-solid {{ $step['icon'] }}"></i>
                            </div>
                            <div class="font-extrabold text-xs">{{ $step['title'] }}</div>
                            <div class="text-[10px] theme-muted mt-0.5">{{ $step['desc'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Per-Item Vendor Fulfillment Breakdown -->
            <div class="space-y-3">
                <h3 class="text-sm font-extrabold uppercase tracking-wider theme-muted">Package Line Items & Vendor Fulfillment Status</h3>
                <div class="divide-y rounded-xl theme-elevated overflow-hidden" style="border-color: var(--border-color);">
                    @foreach($order->items as $item)
                        <div class="p-4 flex flex-wrap items-center justify-between gap-4 text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-800/10 flex items-center justify-center">
                                    @if($item->product && $item->product->image_url)
                                        <img src="{{ $item->product->image_url }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-solid fa-box text-indigo-500"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-extrabold text-sm">{{ $item->product_name ?? ($item->product->name ?? 'Product') }}</div>
                                    <div class="theme-muted">
                                        Sold by: <strong>{{ $item->product->vendor->store_name ?? ($item->product->vendor->name ?? 'Verified Vendor') }}</strong> |
                                        Qty: <strong>{{ $item->quantity }}</strong> × ${{ number_format($item->price, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded text-[10px] font-extrabold uppercase
                                    {{ $item->status === 'delivered' ? 'bg-emerald-600 text-white' : ($item->status === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-indigo-600 text-white') }}">
                                    Vendor Status: {{ $item->status ?? $order->status }}
                                </span>
                                <span class="font-extrabold text-sm text-indigo-500">${{ number_format($item->price * $item->quantity, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="text-center space-y-2">
        <span class="text-xs font-bold uppercase tracking-widest text-indigo-500">Real-Time Logistics Telemetry</span>
        <h1 class="text-3xl font-extrabold"><i class="fa-solid fa-magnifying-glass-location text-indigo-500 mr-2"></i>Track Your Order</h1>
        <p class="text-xs theme-muted">Enter the Tracking ID provided at checkout (e.g., <code class="font-mono text-indigo-500">NXM-...</code>) to inspect live fulfillment status.</p>
    </div>

    <!-- Tracking ID Search Form -->
    <form method="GET" action="{{ route('orders.track') }}" class="theme-card rounded-2xl p-6 flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
        <input type="text"
               name="tracking_number"
               value="{{ $trackingNumber }}"
               required
               placeholder="Enter Tracking ID (e.g. NXM-ABCD-100001) or Order ID..."
               class="flex-1 px-4 py-3 rounded-xl text-sm font-mono uppercase">
        <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold transition">
            <i class="fa-solid fa-satellite-dish mr-1.5"></i> Track Package
        </button>
    </form>

    @if($trackingNumber !== '' && !$order)
        <div class="theme-card rounded-2xl p-10 text-center space-y-3 border-2 border-rose-600">
            <i class="fa-solid fa-circle-exclamation text-4xl text-rose-500"></i>
            <h3 class="text-lg font-extrabold">No Order Found for "{{ $trackingNumber }}"</h3>
            <p class="text-xs theme-muted">Please double-check your tracking number or visit your <a href="{{ route('orders.my') }}" class="text-indigo-500 underline font-bold">Order History</a>.</p>
        </div>
    @endif

    @if($order)
        <div class="theme-card rounded-2xl p-6 md:p-8 space-y-8 border-2 {{ $order->status === 'cancelled' ? 'border-rose-600' : 'border-indigo-600' }}">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-5" style="border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="px-3 py-1 rounded text-xs font-extrabold uppercase {{ $order->status === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-indigo-600 text-white' }}">
                            {{ $order->status }}
                        </span>
                        <span class="font-mono text-sm font-extrabold text-orange-500">{{ $order->tracking_number }}</span>
                    </div>
                    <h2 class="text-xl font-extrabold mt-2">Order #{{ $order->id }} — Placed {{ $order->created_at->format('M d, Y h:i A') }}</h2>
                    @if($order->shipping_address)
                        <p class="text-xs theme-muted mt-1">
                            <i class="fa-solid fa-location-dot text-indigo-500 mr-1"></i>
                            Recipient: <strong>{{ $order->shipping_name }}</strong> ({{ $order->shipping_phone }}) — {{ $order->shipping_address }}, {{ $order->shipping_city }} {{ $order->shipping_postal_code }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-xs theme-muted">Payment Method: <strong class="uppercase">{{ $order->payment_method }}</strong></div>
                    <div class="text-2xl font-extrabold text-emerald-500 mt-0.5">${{ number_format($order->total_price, 2) }}</div>
                </div>
            </div>

            @if($order->status === 'cancelled')
                <div class="p-5 rounded-xl bg-rose-600 text-white text-sm font-bold flex items-center gap-3">
                    <i class="fa-solid fa-ban text-2xl"></i>
                    <div>This order was cancelled and reserved inventory has been returned to stock.</div>
                </div>
            @else
                <!-- Dynamic 5-Step Progress Pipeline -->
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                    @foreach($order->tracking_steps as $step)
                        <div class="p-4 rounded-xl theme-elevated {{ $step['current'] ? 'ring-2 ring-orange-500' : '' }} {{ $step['completed'] ? 'border-2 border-emerald-600' : 'opacity-45' }} flex flex-col items-center text-center">
                            <div class="w-11 h-11 rounded-xl {{ $step['completed'] ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300' }} flex items-center justify-center text-lg mb-2">
                                <i class="fa-solid {{ $step['icon'] }}"></i>
                            </div>
                            <div class="font-extrabold text-xs">{{ $step['title'] }}</div>
                            <div class="text-[10px] theme-muted mt-0.5">{{ $step['desc'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Per-Item Vendor Fulfillment Breakdown -->
            <div class="space-y-3">
                <h3 class="text-sm font-extrabold uppercase tracking-wider theme-muted">Package Line Items & Vendor Fulfillment Status</h3>
                <div class="divide-y rounded-xl theme-elevated overflow-hidden" style="border-color: var(--border-color);">
                    @foreach($order->items as $item)
                        <div class="p-4 flex flex-wrap items-center justify-between gap-4 text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-800/10 flex items-center justify-center">
                                    @if($item->product && $item->product->image_url)
                                        <img src="{{ $item->product->image_url }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-solid fa-box text-indigo-500"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-extrabold text-sm">{{ $item->product_name ?? ($item->product->name ?? 'Product') }}</div>
                                    <div class="theme-muted">
                                        Sold by: <strong>{{ $item->product->vendor->store_name ?? ($item->product->vendor->name ?? 'Verified Vendor') }}</strong> |
                                        Qty: <strong>{{ $item->quantity }}</strong> × ${{ number_format($item->price, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded text-[10px] font-extrabold uppercase
                                    {{ $item->status === 'delivered' ? 'bg-emerald-600 text-white' : ($item->status === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-indigo-600 text-white') }}">
                                    Vendor Status: {{ $item->status ?? $order->status }}
                                </span>
                                <span class="font-extrabold text-sm text-indigo-500">${{ number_format($item->price * $item->quantity, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
