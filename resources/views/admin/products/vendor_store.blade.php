@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="theme-card rounded-2xl p-8 border-2 border-indigo-600 flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-2">
            <span class="px-3 py-1 rounded text-xs font-extrabold bg-emerald-600 text-white uppercase">
                <i class="fa-solid fa-store mr-1"></i> Verified Official Storefront
            </span>
            <h1 class="text-3xl font-extrabold">{{ $vendor->store_name ?? $vendor->name }}</h1>
            <p class="text-xs theme-muted max-w-2xl">{{ $vendor->store_description ?? 'Official verified partner storefront offering genuine products with fast Cash on Delivery fulfillment.' }}</p>
        </div>
        <a href="{{ route('products.index') }}" class="px-4 py-2.5 rounded-xl theme-elevated text-xs font-bold">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to All Stores
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="theme-card rounded-2xl p-5 flex flex-col justify-between">
                <a href="{{ route('products.show', $product) }}" class="block">
                    <div class="h-44 rounded-xl overflow-hidden bg-slate-800/10 flex items-center justify-center mb-3">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-box-open text-4xl text-indigo-500"></i>
                        @endif
                    </div>
                    <h3 class="font-extrabold text-sm line-clamp-1 hover:text-indigo-500">{{ $product->name }}</h3>
                </a>
                <div class="pt-3 mt-3 border-t flex items-center justify-between" style="border-color: var(--border-color);">
                    <span class="text-lg font-extrabold text-indigo-500">${{ number_format($product->price, 2) }}</span>
                    @if($product->stock > 0 && auth()->check() && auth()->user()->role === 'customer')
                        <form method="POST" action="{{ route('cart.add', $product) }}" onsubmit="window.addToCartAjax(event, this)">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                                <i class="fa-solid fa-cart-plus mr-1"></i> Add
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full theme-card rounded-2xl p-12 text-center theme-muted">No products listed in this store yet.</div>
        @endforelse
    </div>

    <div>{{ $products->links() }}</div>
</div>
@endsection
