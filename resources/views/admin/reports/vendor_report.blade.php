@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Screen Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 no-print">
        <div>
            <span class="text-xs font-bold uppercase text-orange-500">Settlement & Payouts</span>
            <h1 class="text-2xl font-extrabold">Vendor GMV & Commission Audit Report</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl theme-card text-xs font-bold">Back to Dashboard</a>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold">
                <i class="fa-solid fa-print mr-1"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    <!-- Print-Only Document Header -->
    <div class="hidden print:block border-b pb-4 mb-4">
        <h1 class="text-2xl font-extrabold">NexaMart — Vendor GMV & Commission Audit Report</h1>
        <p class="text-xs">Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="theme-card rounded-2xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="theme-elevated uppercase text-[10px]">
                    <th class="p-4">Vendor Store</th>
                    <th class="p-4">Listed SKUs</th>
                    <th class="p-4">Units Sold</th>
                    <th class="p-4">Gross GMV</th>
                    <th class="p-4">Platform Fee (10%)</th>
                    <th class="p-4">Net Vendor Payout</th>
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border-color);">
                @foreach($vendors ?? [] as $v)
                    @php $gmv = $v->total_revenue ?? 0; @endphp
                    <tr>
                        <td class="p-4 font-bold">{{ $v->store_name ?? $v->name }}</td>
                        <td class="p-4">{{ $v->products_count ?? $v->products->count() }}</td>
                        <td class="p-4 font-bold">{{ number_format($v->total_units_sold ?? 0) }}</td>
                        <td class="p-4 font-extrabold text-indigo-600">${{ number_format($gmv, 2) }}</td>
                        <td class="p-4 text-orange-600">${{ number_format($gmv * 0.10, 2) }}</td>
                        <td class="p-4 font-extrabold text-emerald-600">${{ number_format($gmv * 0.90, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
