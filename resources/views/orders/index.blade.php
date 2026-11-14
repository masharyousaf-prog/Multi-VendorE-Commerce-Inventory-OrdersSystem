@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-emerald-500">Purchase History</span>
            <h1 class="text-3xl font-extrabold">My Orders ({{ $orders->total() }})</h1>
        </div>
        <a href="{{ route('orders.track') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
            <i class="fa-solid fa-magnifying-glass-location mr-1"></i> Lookup by Tracking ID
        </a>
    </div>

    @forelse($orders as $order)
        <div class="theme-card rounded-2xl p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-4" style="border-color: var(--border-color);">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <span class="font-extrabold text-sm">Order #{{ $order->id }}</span>
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase
                            {{ $order->status === 'delivered' || $order->status === 'completed' ? 'bg-emerald-600 text-white' : ($order->status === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-indigo-600 text-white') }}">
                            {{ $order->status }}
                        </span>
                        <span class="text-xs font-mono font-bold text-orange-500">{{ $order->tracking_number }}</span>
                    </div>
                    <div class="text-xs theme-muted">
                        Placed on {{ $order->created_at->format('M d, Y h:i A') }} • Payment: <strong class="uppercase">{{ $order->payment_method }}</strong>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right mr-2">
                        <div class="text-[10px] uppercase theme-muted font-bold">Order Total</div>
                        <div class="text-lg font-extrabold text-emerald-500">${{ number_format($order->total_price, 2) }}</div>
                    </div>

                    <a href="{{ route('orders.track', ['tracking_number' => $order->tracking_number]) }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                        <i class="fa-solid fa-truck-fast mr-1"></i> Track Live
                    </a>

                    @if($order->status === 'pending')
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Cancel this pending order and restore items to stock?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition">
                                Cancel Order
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($order->items as $item)
                    <div class="p-3 rounded-xl theme-elevated flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold">{{ $item->product_name ?? ($item->product->name ?? 'Product') }}</div>
                            <div class="theme-muted">Qty: {{ $item->quantity }} × ${{ number_format($item->price, 2) }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase theme-card">
                            {{ $item->status ?? $order->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="theme-card rounded-2xl p-12 text-center space-y-3">
            <i class="fa-solid fa-box-open text-4xl text-indigo-500"></i>
            <h3 class="text-lg font-extrabold">You haven't placed any orders yet</h3>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 rounded-xl bg-indigo-600 text-white text-xs font-extrabold">Start Shopping</a>
        </div>
    @endforelse

    <div>
        {{ $orders->links() }}
    </div>
</div>
@endsection
