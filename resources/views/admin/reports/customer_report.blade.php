{{-- resources/views/admin/reports/customer_report.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase text-cyan-400">Business Intelligence</span>
            <h1 class="text-2xl font-extrabold">Customer Lifetime Value (CLV) Report</h1>
        </div>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold">
            <i class="fa-solid fa-print mr-1"></i> Export / Print PDF
        </button>
    </div>

    <div class="glass-panel rounded-3xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-500/20 bg-slate-800/30 uppercase text-[10px]">
                    <th class="p-4">Customer</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Total Orders</th>
                    <th class="p-4">Lifetime Spend</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-500/15">
                @foreach($customers ?? [] as $c)
                    <tr>
                        <td class="p-4 font-bold">{{ $c->name }}</td>
                        <td class="p-4 opacity-75">{{ $c->email }}</td>
                        <td class="p-4 font-bold">{{ $c->orders_count ?? $c->orders->count() }}</td>
                        <td class="p-4 font-extrabold text-emerald-400">${{ number_format($c->orders_sum_total_price ?? $c->orders->sum('total_price'), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
