@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-rose-500">Saved Favourites</span>
            <h1 class="text-3xl font-extrabold"><i class="fa-solid fa-heart text-rose-500 mr-2"></i>My Wishlist</h1>
        </div>
        <a href="{{ route('products.index') }}" class="px-4 py-2.5 rounded-xl theme-card text-xs font-bold hover:border-indigo-500 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Continue Shopping
        </a>
    </div>

    @if($wishlistItems->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($wishlistItems as $item)
                @if($item->product)
                    @php $product = $item->product; @endphp
                    <div class="wishlist-item-card theme-card rounded-2xl p-5 flex flex-col justify-between relative">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-indigo-600 text-white">
                                {{ $product->category->name ?? 'General' }}
                            </span>
                            <form method="POST" action="{{ route('products.wishlist', $product) }}" data-remove-card-on-unwish="true" onsubmit="window.toggleWishlistAjax(event, this)">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-full theme-elevated flex items-center justify-center text-rose-500 hover:bg-rose-600 hover:text-white transition" title="Remove from Wishlist">
                                    <i class="wishlist-icon fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('products.show', $product) }}" class="block my-2">
                            <div class="h-44 rounded-xl overflow-hidden flex items-center justify-center bg-slate-800/10">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-box-open text-4xl text-indigo-500"></i>
                                @endif
                            </div>
                        </a>

                        <div class="space-y-2 mt-2">
                            <div class="text-[11px] theme-muted">
                                <i class="fa-solid fa-store mr-1 text-indigo-500"></i>{{ $product->vendor->store_name ?? $product->vendor->name ?? 'Official Store' }}
                            </div>
                            <a href="{{ route('products.show', $product) }}" class="block font-extrabold text-sm hover:text-indigo-500 line-clamp-1">
                                {{ $product->name }}
                            </a>
                            <div class="pt-3 flex items-center justify-between border-t" style="border-color: var(--border-color);">
                                <span class="text-lg font-extrabold text-indigo-500">${{ number_format($product->price, 2) }}</span>
                                @if($product->stock > 0)
                                    <form method="POST" action="{{ route('cart.add', $product) }}" onsubmit="window.addToCartAjax(event, this)">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                                            <i class="fa-solid fa-cart-plus mr-1"></i> Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <span class="px-2.5 py-1 rounded bg-rose-600 text-white text-[10px] font-bold">Sold Out</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="theme-card rounded-2xl p-12 text-center space-y-4">
            <i class="fa-regular fa-heart text-5xl text-rose-500"></i>
            <h3 class="text-xl font-extrabold">Your wishlist is empty</h3>
            <p class="text-xs theme-muted">Click the heart icon on any product to save it here for later.</p>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 rounded-xl bg-indigo-600 text-white text-xs font-extrabold">
                Explore Products
            </a>
        </div>
    @endif
</div>
@endsection
