@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Screen Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 no-print">
        <div>
            <span class="text-xs font-bold uppercase text-cyan-500">Business Intelligence</span>
            <h1 class="text-2xl font-extrabold">Customer Lifetime Value (CLV) Report</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl theme-card text-xs font-bold">Back to Dashboard</a>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                <i class="fa-solid fa-print mr-1"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    <!-- Print-Only Document Header -->
    <div class="hidden print:block border-b pb-4 mb-4">
        <h1 class="text-2xl font-extrabold">NexaMart — Customer Lifetime Value (CLV) Audit Report</h1>
        <p class="text-xs">Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="theme-card rounded-2xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="theme-elevated uppercase text-[10px]">
                    <th class="p-4">Customer Name</th>
                    <th class="p-4">Email Address</th>
                    <th class="p-4">Total Orders</th>
                    <th class="p-4">Lifetime Spend</th>
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border-color);">
                @foreach($customers ?? [] as $c)
                    <tr>
                        <td class="p-4 font-bold">{{ $c->name }}</td>
                        <td class="p-4 theme-muted">{{ $c->email }}</td>
                        <td class="p-4 font-bold">{{ $c->orders_count ?? $c->orders->count() }}</td>
                        <td class="p-4 font-extrabold text-emerald-600">${{ number_format($c->orders_sum_total_price ?? $c->orders->sum('total_price'), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
