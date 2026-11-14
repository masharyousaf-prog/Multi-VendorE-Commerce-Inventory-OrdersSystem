@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-orange-500">Inventory Management</span>
            <h1 class="text-3xl font-extrabold">My Listed Products ({{ $products->total() }})</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('vendor.dashboard') }}" class="px-4 py-2.5 rounded-xl theme-card text-xs font-bold hover:border-indigo-500 transition">
                <i class="fa-solid fa-chart-line mr-1"></i> Analytics Dashboard
            </a>
            <a href="{{ route('vendor.products.create') }}" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-extrabold transition">
                <i class="fa-solid fa-plus mr-1"></i> Add New Product
            </a>
        </div>
    </div>

    <!-- Search Bar for Vendor Inventory -->
    <form method="GET" action="{{ route('vendor.products.index') }}" class="theme-card rounded-2xl p-4 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search your inventory by title, SKU, or brand..." class="flex-1 px-4 py-2.5 rounded-xl text-xs">
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
            <i class="fa-solid fa-magnifying-glass mr-1"></i> Search
        </button>
        @if(request('q'))
            <a href="{{ route('vendor.products.index') }}" class="px-4 py-2.5 rounded-xl theme-elevated text-xs font-bold">Reset</a>
        @endif
    </form>

    <!-- Vendor Product Listing Table -->
    <div class="theme-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="theme-elevated uppercase text-[10px]">
                        <th class="p-4">Image</th>
                        <th class="p-4">Product Title & SKU</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($products as $product)
                        <tr>
                            <td class="p-4">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-800/10 flex items-center justify-center">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-solid fa-box text-indigo-500"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                <a href="{{ route('products.show', $product) }}" class="font-extrabold text-sm hover:text-indigo-500">{{ $product->name }}</a>
                                <div class="text-[10px] theme-muted font-mono">SKU: {{ $product->sku }} | Brand: {{ $product->brand }}</div>
                            </td>
                            <td class="p-4">{{ $product->category->name ?? 'General' }}</td>
                            <td class="p-4 font-extrabold text-indigo-500">${{ number_format($product->price, 2) }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold {{ $product->stock > 5 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                    {{ $product->stock }} in stock
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <a href="{{ route('vendor.products.edit', $product) }}" class="inline-block px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('vendor.products.delete', $product) }}" class="inline" onsubmit="return confirm('Delete this product listing?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold">
                                        <i class="fa-solid fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center theme-muted">No products found in your inventory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $products->links() }}
    </div>
</div>
@endsection
