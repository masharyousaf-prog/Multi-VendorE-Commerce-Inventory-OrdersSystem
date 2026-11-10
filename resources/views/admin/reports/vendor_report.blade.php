{{-- resources/views/admin/reports/vendor_report.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase text-orange-400">Settlement & Payouts</span>
            <h1 class="text-2xl font-extrabold">Vendor GMV & Commission Audit Report</h1>
        </div>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold">
            <i class="fa-solid fa-file-export mr-1"></i> Export Audit Report
        </button>
    </div>

    <div class="glass-panel rounded-3xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-500/20 bg-slate-800/30 uppercase text-[10px]">
                    <th class="p-4">Vendor Store</th>
                    <th class="p-4">Listed SKUs</th>
                    <th class="p-4">Gross GMV</th>
                    <th class="p-4">Platform Fee (10%)</th>
                    <th class="p-4">Net Vendor Payout</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-500/15">
                @foreach($vendors ?? [] as $v)
                    @php
                        $gmv = $v->total_revenue ?? 0;
                    @endphp
                    <tr>
                        <td class="p-4 font-bold">{{ $v->store_name ?? $v->name }}</td>
                        <td class="p-4">{{ $v->products_count ?? $v->products->count() }}</td>
                        <td class="p-4 font-extrabold text-indigo-400">${{ number_format($gmv, 2) }}</td>
                        <td class="p-4 text-orange-400">${{ number_format($gmv * 0.10, 2) }}</td>
                        <td class="p-4 font-extrabold text-emerald-400">${{ number_format($gmv * 0.90, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
