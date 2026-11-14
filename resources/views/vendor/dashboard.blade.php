@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ showSettings: false }">

    <!-- New Order Alert Banner for Vendor -->
    @if(($pendingOrdersCount ?? 0) > 0)
        <div class="p-5 rounded-2xl bg-orange-600 text-white flex flex-wrap items-center justify-between gap-4 shadow-lg">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-xl bg-slate-950/30 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-bell"></i>
                </span>
                <div>
                    <h3 class="font-extrabold text-base">You have {{ $pendingOrdersCount }} new/active order item(s) awaiting fulfillment!</h3>
                    <p class="text-xs opacity-90">Pack and update the shipment status so your customers can track their delivery live.</p>
                </div>
            </div>
            <a href="{{ route('vendor.orders') }}" class="px-5 py-2.5 rounded-xl bg-slate-950 hover:bg-slate-900 text-white text-xs font-extrabold transition">
                Manage Orders Now <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-orange-500">Seller Central Analytics</span>
            <h1 class="text-3xl font-extrabold">{{ $vendor->store_name ?? $vendor->name . ' Storefront' }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="showSettings = !showSettings" class="px-4 py-2.5 rounded-xl theme-card hover:border-indigo-500 text-xs font-bold transition">
                <i class="fa-solid fa-gear mr-1 text-indigo-500"></i> Store Settings
            </button>
            @if($vendor->store_slug)
                <a href="{{ route('vendor.store', $vendor->store_slug) }}" class="px-4 py-2.5 rounded-xl theme-card hover:border-emerald-500 text-xs font-bold transition">
                    <i class="fa-solid fa-up-right-from-square mr-1 text-emerald-500"></i> Public Storefront
                </a>
            @endif
            <a href="{{ route('vendor.orders') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold transition">
                <i class="fa-solid fa-truck-fast mr-1"></i> Orders ({{ $pendingOrdersCount ?? 0 }} New)
            </a>
            <a href="{{ route('vendor.products.index') }}" class="px-4 py-2.5 rounded-xl theme-card hover:border-indigo-500 text-xs font-extrabold transition">
                <i class="fa-solid fa-boxes-stacked mr-1 text-indigo-500"></i> My Products ({{ $products->count() }})
            </a>
            <a href="{{ route('vendor.products.create') }}" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-extrabold transition">
                <i class="fa-solid fa-plus mr-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Collapsible Storefront Settings Form -->
    <form x-show="showSettings" x-transition method="POST" action="{{ route('vendor.settings.update') }}" class="theme-card rounded-2xl p-6 space-y-4 border-2 border-indigo-600">
        @csrf
        @method('PUT')
        <h3 class="font-extrabold text-base">Customize Storefront Profile</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold block mb-1">Official Storefront Name</label>
                <input type="text" name="store_name" value="{{ $vendor->store_name ?? $vendor->name }}" required class="w-full px-4 py-2.5 rounded-xl text-xs">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Store Description</label>
                <input type="text" name="store_description" value="{{ $vendor->store_description }}" placeholder="Describe your store specialty & warranty..." class="w-full px-4 py-2.5 rounded-xl text-xs">
            </div>
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-extrabold">Save Store Settings</button>
    </form>

    <!-- Vendor KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Gross Sales Revenue</div>
            <div class="text-2xl font-extrabold text-emerald-500 mt-1">${{ number_format($grossEarnings, 2) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Net Payout (After {{ $vendor->commission_rate ?? 10 }}% Fee)</div>
            <div class="text-2xl font-extrabold text-indigo-500 mt-1">${{ number_format($netEarnings, 2) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Total Units Sold</div>
            <div class="text-2xl font-extrabold text-orange-500 mt-1">{{ number_format($totalUnitsSold) }}</div>
        </div>
        <div class="theme-card rounded-2xl p-5">
            <div class="text-xs theme-muted font-semibold">Low Stock Alerts (≤ 5)</div>
            <div class="text-2xl font-extrabold text-rose-500 mt-1">{{ $lowStockCount }} SKUs</div>
        </div>
    </div>

    <!-- Row 1: 2 Large 6-Column Charts (350px Height) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">14-Day Store Earnings & Units Sold (Dual Axis)</h3>
            <div id="vendorSalesChart" class="h-[350px]"></div>
        </div>
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">6-Month Gross Sales vs. Net Vendor Payout</h3>
            <div id="vendorMonthlyChart" class="h-[350px]"></div>
        </div>
    </div>

    <!-- Row 2: 2 Large 6-Column Charts (350px Height) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">Top Performing SKUs by Revenue</h3>
            <div id="vendorTopProductsChart" class="h-[350px]"></div>
        </div>
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">Revenue Share by Department Category</h3>
            <div id="vendorCategoryChart" class="h-[350px]"></div>
        </div>
    </div>

    <!-- Row 3: 2 Large 6-Column Charts (340px Height) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">Order Fulfillment Pipeline Breakdown</h3>
            <div id="vendorOrderStatusChart" class="h-[340px]"></div>
        </div>
        <div class="lg:col-span-6 theme-card rounded-2xl p-6">
            <h3 class="font-extrabold text-base mb-4">Inventory Stock Health Distribution</h3>
            <div id="vendorStockChart" class="h-[340px]"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.getChartThemeConfig();

    const salesChart = new ApexCharts(document.querySelector("#vendorSalesChart"), {
        chart: { height: 340, type: 'line', background: 'transparent', foreColor: cfg.foreColor },
        series: [
            { name: 'Revenue ($)', type: 'line', data: @json($chartRevenue) },
            { name: 'Units Sold', type: 'line', data: @json($chartUnits) }
        ],
        xaxis: { categories: @json($chartLabels) },
        colors: ['#059669', '#6366f1'],
        stroke: { curve: 'smooth', width: [3, 3] },
        markers: { size: 4 },
        grid: { borderColor: cfg.gridColor },
        yaxis: [
            { title: { text: 'Revenue ($)' }, labels: { formatter: (v) => '$' + Math.round(v) } },
            { opposite: true, title: { text: 'Units Sold' }, labels: { formatter: (v) => Math.round(v) } }
        ],
        theme: { mode: cfg.mode }
    });
    salesChart.render();

    const monthlyChart = new ApexCharts(document.querySelector("#vendorMonthlyChart"), {
        chart: { height: 340, type: 'bar', background: 'transparent', foreColor: cfg.foreColor },
        series: [
            { name: 'Gross GMV ($)', data: @json($monthlyGrossData) },
            { name: 'Net Payout ($)', data: @json($monthlyNetData) }
        ],
        xaxis: { categories: @json($monthLabels) },
        colors: ['#4f46e5', '#10b981'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
        grid: { borderColor: cfg.gridColor },
        theme: { mode: cfg.mode }
    });
    monthlyChart.render();

    const topProdChart = new ApexCharts(document.querySelector("#vendorTopProductsChart"), {
        chart: { height: 340, type: 'bar', background: 'transparent', foreColor: cfg.foreColor },
        plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
        series: [{ name: 'Revenue ($)', data: @json($topProducts->pluck('revenue')->map(fn($v) => (float)$v)) }],
        xaxis: { categories: @json($topProducts->map(fn($p) => \Illuminate\Support\Str::limit($p->product->name ?? 'SKU', 28))) },
        grid: { borderColor: cfg.gridColor },
        colors: ['#ea580c'],
        theme: { mode: cfg.mode }
    });
    topProdChart.render();

    const catChart = new ApexCharts(document.querySelector("#vendorCategoryChart"), {
        chart: { height: 340, type: 'donut', background: 'transparent', foreColor: cfg.foreColor },
        series: @json($vendorCategorySales->pluck('revenue')->map(fn($v) => (float)$v)),
        labels: @json($vendorCategorySales->pluck('name')),
        colors: ['#4f46e5', '#059669', '#ea580c', '#0284c7', '#7c3aed', '#eab308'],
        legend: { position: 'bottom' },
        theme: { mode: cfg.mode }
    });
    catChart.render();

    const statusChart = new ApexCharts(document.querySelector("#vendorOrderStatusChart"), {
        chart: { height: 330, type: 'polarArea', background: 'transparent', foreColor: cfg.foreColor },
        series: @json($vendorOrderStatus->values()),
        labels: @json($vendorOrderStatus->keys()),
        colors: ['#f59e0b', '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#ef4444'],
        legend: { position: 'bottom' },
        theme: { mode: cfg.mode }
    });
    statusChart.render();

    const stockChart = new ApexCharts(document.querySelector("#vendorStockChart"), {
        chart: { height: 330, type: 'donut', background: 'transparent', foreColor: cfg.foreColor },
        series: @json(array_values($stockHealth)),
        labels: @json(array_keys($stockHealth)),
        colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
        legend: { position: 'bottom' },
        theme: { mode: cfg.mode }
    });
    stockChart.render();

    window.addEventListener('theme-changed', () => {
        const next = window.getChartThemeConfig();
        [salesChart, monthlyChart, topProdChart, catChart, statusChart, stockChart].forEach(c => {
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
