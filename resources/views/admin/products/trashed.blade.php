{{-- resources/views/admin/products/trashed.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold text-rose-400"><i class="fa-solid fa-recycle mr-2"></i>Soft-Deleted Products Vault</h1>
        <a href="{{ route('admin.products') }}" class="px-4 py-2 rounded-xl glass-panel text-xs font-bold">Back to Active Products</a>
    </div>

    <div class="glass-panel rounded-3xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-500/20 bg-slate-800/30 uppercase text-[10px]">
                    <th class="p-4">Product</th>
                    <th class="p-4">Vendor</th>
                    <th class="p-4">Deleted At</th>
                    <th class="p-4 text-right">Recovery Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-500/15">
                @forelse($products as $product)
                    <tr>
                        <td class="p-4 font-bold">{{ $product->name }}</td>
                        <td class="p-4">{{ $product->vendor->name ?? 'Vendor' }}</td>
                        <td class="p-4 opacity-70">{{ $product->deleted_at->diffForHumans() }}</td>
                        <td class="p-4 text-right space-x-2">
                            <form method="POST" action="{{ route('admin.products.restore', $product->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500/15 text-emerald-400 font-bold">Restore</button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.forceDelete', $product->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-600 text-white font-bold">Purge Forever</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center opacity-60">Trash vault is empty.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
