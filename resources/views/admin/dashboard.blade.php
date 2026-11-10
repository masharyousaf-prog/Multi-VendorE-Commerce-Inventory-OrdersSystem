@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-400">Executive Control Center</span>
            <h1 class="text-3xl font-extrabold">Global Marketplace Telemetry</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users') }}" class="px-4 py-2.5 rounded-xl glass-panel text-xs font-bold hover:border-indigo-500 transition">
                <i class="fa-solid fa-users mr-1"></i> Manage Users
            </a>
            <a href="{{ route('admin.products') }}" class="px-4 py-2.5 rounded-xl glass-panel text-xs font-bold hover:border-indigo-500 transition">
                <i class="fa-solid fa-boxes-stacked mr-1"></i> All Products
            </a>
        </div>
    </div>

    <!-- 6 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Gross GMV</div>
            <div class="text-2xl font-extrabold text-emerald-400 mt-1">${{ number_format($totalRevenue, 2) }}</div>
            <div class="text-[10px] text-emerald-400 mt-1"><i class="fa-solid fa-arrow-trend-up"></i> Live Orders</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Platform Commission</div>
            <div class="text-2xl font-extrabold text-indigo-400 mt-1">${{ number_format($platformCommission, 2) }}</div>
            <div class="text-[10px] opacity-60 mt-1">10% Take Rate</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Total Orders</div>
            <div class="text-2xl font-extrabold mt-1">{{ number_format($totalOrders) }}</div>
            <div class="text-[10px] opacity-60 mt-1">All Channels</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Active Catalogue</div>
            <div class="text-2xl font-extrabold text-orange-400 mt-1">{{ number_format($totalProducts) }}</div>
            <div class="text-[10px] opacity-60 mt-1">SKUs Listed</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Verified Vendors</div>
            <div class="text-2xl font-extrabold text-purple-400 mt-1">{{ number_format($totalVendors) }}</div>
            <div class="text-[10px] opacity-60 mt-1">Auto-Approved</div>
        </div>
        <div data-tilt class="glass-panel rounded-2xl p-5">
            <div class="text-xs opacity-65">Customers</div>
            <div class="text-2xl font-extrabold text-cyan-400 mt-1">{{ number_format($totalCustomers) }}</div>
            <div class="text-[10px] opacity-60 mt-1">Active Buyers</div>
        </div>
    </div>

    <!-- Primary Charts Row: 14-Day Revenue & Category Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">14-Day GMV Revenue & Order Velocity</h3>
            <div id="adminRevenueChart" class="h-80"></div>
        </div>
        <div class="lg:col-span-4 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">Revenue by Department</h3>
            <div id="adminCategoryChart" class="h-80"></div>
        </div>
    </div>

    <!-- Secondary Charts Row: Top Vendors, Order Pipeline & User Growth -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">Top Vendors by GMV</h3>
            <div id="adminTopVendorsChart" class="h-72"></div>
        </div>
        <div class="lg:col-span-4 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">Order Fulfillment Funnel</h3>
            <div id="adminOrderStatusChart" class="h-72"></div>
        </div>
        <div class="lg:col-span-4 glass-panel rounded-3xl p-6">
            <h3 class="font-extrabold text-base mb-4">6-Month User Acquisition</h3>
            <div id="adminUserGrowthChart" class="h-72"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const commonTheme = { mode: 'dark' };

    // 1. Dual-Axis Revenue & Orders Area/Column Chart
    new ApexCharts(document.querySelector("#adminRevenueChart"), {
        chart: { height: 310, type: 'line', toolbar: { show: true }, background: 'transparent' },
        series: [
            { name: 'Revenue ($)', type: 'area', data: @json($revenueTrendData) },
            { name: 'Orders', type: 'column', data: @json($orderTrendData) }
        ],
        stroke: { width: [3, 0], curve: 'smooth' },
        colors: ['#6366f1', '#f97316'],
        fill: { type: ['gradient', 'solid'], gradient: { opacityFrom: 0.45, opacityTo: 0.05 } },
        labels: @json($revenueTrendLabels),
        yaxis: [
            { title: { text: 'Revenue ($)' } },
            { opposite: true, title: { text: 'Orders Count' } }
        ],
        theme: commonTheme
    }).render();

    // 2. Category GMV Donut Chart
    new ApexCharts(document.querySelector("#adminCategoryChart"), {
        chart: { height: 310, type: 'donut', background: 'transparent' },
        series: @json($categorySales->pluck('revenue')->map(fn($v) => (float)$v)),
        labels: @json($categorySales->pluck('name')),
        colors: ['#6366f1', '#f97316', '#10b981', '#ec4899', '#06b6d4', '#eab308'],
        legend: { position: 'bottom' },
        theme: commonTheme
    }).render();

    // 3. Top Vendors Horizontal Bar Chart
    new ApexCharts(document.querySelector("#adminTopVendorsChart"), {
        chart: { height: 280, type: 'bar', background: 'transparent' },
        plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
        series: [{ name: 'GMV ($)', data: @json($topVendors->pluck('total_gmv')->map(fn($v) => (float)$v)) }],
        xaxis: { categories: @json($topVendors->map(fn($v) => $v->store_name ?? $v->name)) },
        colors: ['#10b981'],
        theme: commonTheme
    }).render();

    // 4. Order Status Polar Area Chart
    new ApexCharts(document.querySelector("#adminOrderStatusChart"), {
        chart: { height: 280, type: 'polarArea', background: 'transparent' },
        series: @json($orderStatusBreakdown->values()),
        labels: @json($orderStatusBreakdown->keys()),
        colors: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
        legend: { position: 'bottom' },
        theme: commonTheme
    }).render();

    // 5. 6-Month User Growth Stacked Column Chart
    new ApexCharts(document.querySelector("#adminUserGrowthChart"), {
        chart: { height: 280, type: 'bar', stacked: true, background: 'transparent' },
        series: [
            { name: 'Customers', data: @json($customerGrowth) },
            { name: 'Vendors', data: @json($vendorGrowth) }
        ],
        xaxis: { categories: @json($monthLabels) },
        colors: ['#06b6d4', '#a855f7'],
        theme: commonTheme
    }).render();
});
</script>
@endsection
