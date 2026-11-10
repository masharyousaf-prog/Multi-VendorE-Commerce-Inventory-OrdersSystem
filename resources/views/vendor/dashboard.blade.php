@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-orange-400">Seller Central</span>
            <h1 class="text-3xl font-extrabold">{{ $vendor->store_name ?? $vendor->name . ' Storefront' }}</h1>
        </div>
        <a href="{{ route('vendor.products.create') }}" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-orange-500 to-pink-600 text-white text-xs font-extrabold shadow-lg hover:scale-105 transition">
            <i class="fa-solid fa-plus mr-1"></i> Add New Product
        </a>
    </div>

    <!-- Vendor KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Gross Sales</div>
            <div class="text-2xl font-extrabold text-emerald-400 mt-1">${{ number_format($grossEarnings, 2) }}</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Net Payout (After {{ $vendor->commission_rate ?? 10 }}% Fee)</div>
            <div class="text-2xl font-extrabold text-indigo-400 mt-1">${{ number_format($netEarnings, 2) }}</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Total Units Sold</div>
            <div class="text-2xl font-extrabold text-orange-400 mt-1">{{ number_format($totalUnitsSold) }}</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Low Stock Alerts (≤ 5)</div>
            <div class="text-2xl font-extrabold text-rose-400 mt-1">{{ $lowStockCount }} SKUs</div>
        </div>
    </div>

    <!-- Multi-Graph Vendor Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">14-Day Store Earnings & Units</h3>
            <div id="vendorSalesChart" class="h-72"></div>
        </div>
        <div class="lg:col-span-3 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">Top SKUs by Revenue</h3>
            <div id="vendorTopProductsChart" class="h-72"></div>
        </div>
        <div class="lg:col-span-3 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">Inventory Health</h3>
            <div id="vendorStockChart" class="h-72"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const theme = { mode: 'dark' };

    new ApexCharts(document.querySelector("#vendorSalesChart"), {
        chart: { height: 280, type: 'area', background: 'transparent' },
        series: [
            { name: 'Revenue ($)', data: @json($chartRevenue) },
            { name: 'Units Sold', data: @json($chartUnits) }
        ],
        xaxis: { categories: @json($chartLabels) },
        colors: ['#10b981', '#6366f1'],
        stroke: { curve: 'smooth', width: 3 },
        theme
    }).render();

    new ApexCharts(document.querySelector("#vendorTopProductsChart"), {
        chart: { height: 280, type: 'bar', background: 'transparent' },
        plotOptions: { bar: { horizontal: true, borderRadius: 5 } },
        series: [{ name: 'Revenue ($)', data: @json($topProducts->pluck('revenue')->map(fn($v) => (float)$v)) }],
        xaxis: { categories: @json($topProducts->map(fn($p) => \Illuminate\Support\Str::limit($p->product->name ?? 'SKU', 16))) },
        colors: ['#f97316'],
        theme
    }).render();

    new ApexCharts(document.querySelector("#vendorStockChart"), {
        chart: { height: 280, type: 'donut', background: 'transparent' },
        series: @json(array_values($stockHealth)),
        labels: @json(array_keys($stockHealth)),
        colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
        legend: { position: 'bottom' },
        theme
    }).render();
});
</script>
@endsection
