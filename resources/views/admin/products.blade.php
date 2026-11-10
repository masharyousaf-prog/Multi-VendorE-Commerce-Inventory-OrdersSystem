{{-- resources/views/admin/products.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-extrabold">Global Product Catalogue Moderation</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.products.trashed') }}" class="px-4 py-2 rounded-xl bg-rose-500/15 text-rose-400 text-xs font-bold">
                <i class="fa-solid fa-trash-arrow-up mr-1"></i> Trashed Vault
            </a>
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl glass-panel text-xs font-bold">Dashboard</a>
        </div>
    </div>

    <div class="glass-panel rounded-3xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-500/20 bg-slate-800/30 uppercase text-[10px]">
                    <th class="p-4">Product</th>
                    <th class="p-4">Vendor</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Stock</th>
                    <th class="p-4 text-right">Moderation</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-500/15">
                @foreach($products as $product)
                    <tr class="hover:bg-indigo-500/5">
                        <td class="p-4 font-bold">{{ $product->name }}</td>
                        <td class="p-4 text-orange-400">{{ $product->vendor->store_name ?? $product->vendor->name ?? 'N/A' }}</td>
                        <td class="p-4 font-extrabold text-indigo-400">${{ number_format($product->price, 2) }}</td>
                        <td class="p-4">{{ $product->stock }} units</td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('admin.products.delete', $product) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-500/15 text-rose-400 hover:bg-rose-600 hover:text-white font-bold transition">
                                    Move to Trash
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
