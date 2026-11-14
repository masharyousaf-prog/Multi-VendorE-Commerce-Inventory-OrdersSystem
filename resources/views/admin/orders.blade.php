@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-500">Platform Logistics</span>
            <h1 class="text-2xl font-extrabold">Global Orders Management ({{ $orders->total() }})</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl theme-card text-xs font-bold">Back to Dashboard</a>
    </div>

    <form method="GET" action="{{ route('admin.orders') }}" class="theme-card rounded-2xl p-4 flex flex-wrap gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by Tracking ID (NXM-...) or Order #..." class="flex-1 px-4 py-2.5 rounded-xl text-xs">
        <select name="status" class="px-4 py-2.5 rounded-xl text-xs font-bold">
            <option value="">All Statuses</option>
            @foreach(['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold">Filter</button>
    </form>

    <div class="theme-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="theme-elevated uppercase text-[10px]">
                        <th class="p-4">Order / Tracking</th>
                        <th class="p-4">Customer & Destination</th>
                        <th class="p-4">Items</th>
                        <th class="p-4">Total (COD)</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Override Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($orders as $order)
                        <tr>
                            <td class="p-4">
                                <div class="font-extrabold">Order #{{ $order->id }}</div>
                                <a href="{{ route('orders.track', ['tracking_number' => $order->tracking_number]) }}" class="font-mono text-indigo-500 hover:underline">{{ $order->tracking_number }}</a>
                            </td>
                            <td class="p-4">
                                <div class="font-bold">{{ $order->shipping_name ?? ($order->user->name ?? 'Customer') }}</div>
                                <div class="text-[11px] theme-muted">{{ $order->shipping_city ?? $order->user->email }}</div>
                            </td>
                            <td class="p-4">{{ $order->items->sum('quantity') }} unit(s)</td>
                            <td class="p-4 font-extrabold text-emerald-500">${{ number_format($order->total_price, 2) }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-indigo-600 text-white">{{ $order->status }}</span>
                            </td>
                            <td class="p-4 text-right">
                                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="px-2.5 py-1.5 rounded-lg text-xs font-bold">
                                        @foreach(['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled'] as $st)
                                            <option value="{{ $st }}" @selected($order->status === $st)>{{ ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $orders->links() }}</div>
</div>
@endsection
