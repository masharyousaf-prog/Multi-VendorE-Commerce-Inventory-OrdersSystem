@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-500">Executive Control Center</span>
            <h1 class="text-3xl font-extrabold">Global Marketplace Telemetry</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.reports.customers') }}" class="px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                <i class="fa-solid fa-file-pdf mr-1"></i> Customer PDF Report
            </a>
            <a href="{{ route('admin.reports.vendors') }}" class="px-3.5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold transition">
                <i class="fa-solid fa-file-pdf mr-1"></i> Vendor Audit PDF
            </a>
            <a href="{{ route('admin.orders') }}" class="px-3.5 py-2.5 rounded-xl theme-card text-xs font-bold hover:border-indigo-500 transition">
                <i class="fa-solid fa-truck-ramp-box mr-1 text-indigo-500"></i> All Orders
            </a>
            <a href="{{ route('admin.coupons') }}" class="px-3.5 py-2.5 rounded-xl theme-card text-xs font-bold hover:border-orange-500 transition">
                <i class="fa-solid fa-ticket mr-1 text-orange-500"></i> Coupons
            </a>
            <a href="{{ route('admin.users') }}" class="px-3.5 py-2.5 rounded-xl theme-card text-xs font-bold hover:border-indigo-500 transition">
                <i class="fa-solid fa-users mr-1"></i> Users
            </a>
            <a href="{{ route('admin.products') }}" class="px-3.5 py-2.5 rounded-xl theme-card text-xs font-bold hover:border-indigo-500 transition">
                <i class="fa-solid fa-boxes-stacked mr-1"></i> Products
            </a>
        </div>
    </div>

    <!-- 6 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Gross GMV</div>
            <div class="text-2xl font-extrabold text-emerald-500 mt-1">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Platform Commission</div>
            <div class="text-2xl font-extrabold text-indigo-500 mt-1">${{ number_format($platformCommission, 2) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Total Orders</div>
            <div class="text-2xl font-extrabold mt-1">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Active Catalogue</div>
            <div class="text-2xl font-extrabold text-orange-500 mt-1">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Verified Vendors</div>
            <div class="text-2xl font-extrabold text-purple-500 mt-1">{{ number_format($totalVendors) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Customers</div>
            <div class="text-2xl font-extrabold text-cyan-500 mt-1">{{ number_format($totalCustomers) }}</div>
        </div>
    </div>

    <!-- Row 1: Full-Width 14-Day Dual-Line Graph -->
    <div class="theme-card rounded-2xl p-6">
        <h3 class="font-extrabold text-base mb-4">14-Day GMV Revenue & Order Velocity (Dual Line)</h3>
        <div id="adminRevenueChart" class="h-[360px]"></div>
    </div>

    <!-- Row 2: 2 Large 6-Column Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">Revenue Share by Department</h3>
            <div id="adminCategoryChart" class="h-[350px]"></div>
        </div>
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">Top Performing Vendors by GMV</h3>
            <div id="adminTopVendorsChart" class="h-[350px]"></div>
        </div>
    </div>

    <!-- Row 3: 2 Large 6-Column Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">Order Fulfillment Pipeline</h3>
            <div id="adminOrderStatusChart" class="h-[350px]"></div>
        </div>
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">6-Month User Acquisition Growth</h3>
            <div id="adminUserGrowthChart" class="h-[350px]"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.getChartThemeConfig();

    const revChart = new ApexCharts(document.querySelector("#adminRevenueChart"), {
        chart: { height: 350, type: 'line', toolbar: { show: true }, background: 'transparent', foreColor: cfg.foreColor },
        series: [
            { name: 'Revenue ($)', type: 'line', data: @json($revenueTrendData) },
            { name: 'Orders Count', type: 'line', data: @json($orderTrendData) }
        ],
        stroke: { width: [3, 3], curve: 'smooth' },
        markers: { size: 4 },
        colors: ['#4f46e5', '#ea580c'],
        labels: @json($revenueTrendLabels),
        grid: { borderColor: cfg.gridColor },
        yaxis: [
            { title: { text: 'Revenue ($)' }, labels: { formatter: (v) => '$' + Math.round(v) } },
            { opposite: true, title: { text: 'Orders Count' }, labels: { formatter: (v) => Math.round(v) } }
        ],
        theme: { mode: cfg.mode }
    });
    revChart.render();

    const catChart = new ApexCharts(document.querySelector("#adminCategoryChart"), {
        chart: { height: 340, type: 'donut', background: 'transparent', foreColor: cfg.foreColor },
        series: @json($categorySales->pluck('revenue')->map(fn($v) => (float)$v)),
        labels: @json($categorySales->pluck('name')),
        colors: ['#4f46e5', '#ea580c', '#059669', '#d946ef', '#0284c7', '#eab308', '#7c3aed', '#e11d48'],
        legend: { position: 'bottom' },
        theme: { mode: cfg.mode }
    });
    catChart.render();

    const vendorChart = new ApexCharts(document.querySelector("#adminTopVendorsChart"), {
        chart: { height: 340, type: 'bar', background: 'transparent', foreColor: cfg.foreColor },
        plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
        series: [{ name: 'GMV ($)', data: @json($topVendors->pluck('total_gmv')->map(fn($v) => (float)$v)) }],
        xaxis: { categories: @json($topVendors->map(fn($v) => $v->store_name ?? $v->name)) },
        grid: { borderColor: cfg.gridColor },
        colors: ['#059669'],
        theme: { mode: cfg.mode }
    });
    vendorChart.render();

    const statusChart = new ApexCharts(document.querySelector("#adminOrderStatusChart"), {
        chart: { height: 340, type: 'polarArea', background: 'transparent', foreColor: cfg.foreColor },
        series: @json($orderStatusBreakdown->values()),
        labels: @json($orderStatusBreakdown->keys()),
        colors: ['#f59e0b', '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#ef4444'],
        legend: { position: 'bottom' },
        theme: { mode: cfg.mode }
    });
    statusChart.render();

    const growthChart = new ApexCharts(document.querySelector("#adminUserGrowthChart"), {
        chart: { height: 340, type: 'bar', stacked: true, background: 'transparent', foreColor: cfg.foreColor },
        series: [
            { name: 'Customers', data: @json($customerGrowth) },
            { name: 'Vendors', data: @json($vendorGrowth) }
        ],
        xaxis: { categories: @json($monthLabels) },
        grid: { borderColor: cfg.gridColor },
        colors: ['#0284c7', '#9333ea'],
        theme: { mode: cfg.mode }
    });
    growthChart.render();

    window.addEventListener('theme-changed', () => {
        const next = window.getChartThemeConfig();
        [revChart, catChart, vendorChart, statusChart, growthChart].forEach(c => {
            c.updateOptions({
                chart: { foreColor: next.foreColor },
                grid: { borderColor: next.gridColor },
                theme: { mode: next.mode }
            });
        });
    });
});
</script>
@endsection
