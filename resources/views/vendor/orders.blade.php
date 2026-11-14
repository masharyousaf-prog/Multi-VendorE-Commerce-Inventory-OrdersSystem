@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-orange-500">Seller Fulfillment Center</span>
            <h1 class="text-3xl font-extrabold">Incoming Customer Orders</h1>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="px-4 py-2.5 rounded-xl theme-card text-xs font-bold">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Analytics Hub
        </a>
    </div>

    <!-- Status Filter Pills -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('vendor.orders') }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ !request('status') ? 'bg-indigo-600 text-white' : 'theme-card' }}">
            All Orders ({{ $statusCounts->sum() }})
        </a>
        @foreach(['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled'] as $st)
            <a href="{{ route('vendor.orders', ['status' => $st]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase {{ request('status') === $st ? 'bg-orange-600 text-white' : 'theme-card' }}">
                {{ $st }} ({{ $statusCounts[$st] ?? 0 }})
            </a>
        @endforeach
    </div>

    <!-- Orders Fulfillment Table -->
    <div class="theme-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="theme-elevated uppercase text-[10px]">
                        <th class="p-4">Order & Tracking ID</th>
                        <th class="p-4">Product & Qty</th>
                        <th class="p-4">Customer & Delivery Address</th>
                        <th class="p-4">COD Amount</th>
                        <th class="p-4">Current Status</th>
                        <th class="p-4 text-right">Update Fulfillment</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($orderItems as $item)
                        <tr>
                            <td class="p-4">
                                <div class="font-extrabold text-sm">Order #{{ $item->order_id }}</div>
                                <a href="{{ route('orders.track', ['tracking_number' => $item->order->tracking_number ?? '']) }}" class="font-mono text-[11px] text-indigo-500 hover:underline">
                                    {{ $item->order->tracking_number ?? 'N/A' }}
                                </a>
                                <div class="text-[10px] theme-muted">{{ $item->created_at->format('M d, Y h:i A') }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-sm">{{ $item->product_name ?? ($item->product->name ?? 'SKU') }}</div>
                                <div class="theme-muted">Qty: <strong>{{ $item->quantity }}</strong> × ${{ number_format($item->price, 2) }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold">{{ $item->order->shipping_name ?? ($item->order->user->name ?? 'Customer') }}</div>
                                @if($item->order && $item->order->shipping_phone)
                                    <div class="text-[11px] text-emerald-500 font-semibold"><i class="fa-solid fa-phone mr-1"></i>{{ $item->order->shipping_phone }}</div>
                                    <div class="text-[11px] theme-muted">{{ $item->order->shipping_address }}, {{ $item->order->shipping_city }}</div>
                                @else
                                    <div class="text-[11px] theme-muted">{{ $item->order->user->email ?? '' }}</div>
                                @endif
                            </td>
                            <td class="p-4 font-extrabold text-emerald-500">${{ number_format($item->price * $item->quantity, 2) }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase
                                    {{ $item->status === 'delivered' ? 'bg-emerald-600 text-white' : ($item->status === 'cancelled' ? 'bg-rose-600 text-white' : ($item->status === 'pending' ? 'bg-orange-600 text-white' : 'bg-indigo-600 text-white')) }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <form method="POST" action="{{ route('vendor.orders.status', $item) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="px-3 py-1.5 rounded-lg text-xs font-bold">
                                        @foreach(['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled'] as $st)
                                            <option value="{{ $st }}" @selected($item->status === $st)>{{ ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center theme-muted">No orders matching this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $orderItems->links() }}
    </div>
</div>
@endsection
