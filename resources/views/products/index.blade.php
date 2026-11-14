@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- 1. THEME-ADAPTIVE HERO BENTO GRID (Adapts to Light, Dark & Cyber Modes) -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 theme-card rounded-2xl p-8 md:p-10 border-2 border-indigo-600 flex flex-col justify-between min-h-[300px]">
            <div class="max-w-xl space-y-4">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg text-xs font-bold bg-orange-600 text-white">
                    <i class="fa-solid fa-fire-flame-curved"></i> GLOBAL SUPER DEALS FESTIVAL
                </span>
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight">
                    Discover Top Brands & Verified Global Stores
                </h1>
                <p class="theme-muted text-sm md:text-base">
                    Shop 500+ curated products across 8 departments with express delivery, verified customer reviews, and buyer protection.
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="#marketplace-catalogue" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition">
                        Explore Catalogue <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                    <a href="?flash_deals=1#marketplace-catalogue" class="px-6 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm transition">
                        <i class="fa-solid fa-bolt mr-1"></i> Lightning Offers
                    </a>
                </div>
            </div>
        </div>

        <!-- Side Promotional Cards -->
        <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-6">
            <div class="theme-card rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-emerald-600 text-white">Verified Sellers</span>
                    <h3 class="text-xl font-extrabold mt-3">Sell on NexaMart</h3>
                    <p class="text-xs theme-muted mt-1">Launch your custom vendor storefront with real-time business analytics.</p>
                </div>
                <a href="{{ route('register') }}" class="mt-4 inline-flex items-center text-xs font-bold text-emerald-500 hover:underline">
                    Open Your Storefront <i class="fa-solid fa-chevron-right ml-1"></i>
                </a>
            </div>

            <div class="theme-card rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-orange-600 text-white">Voucher Center</span>
                    <h3 class="text-xl font-extrabold mt-3">20% OFF Orders $50+</h3>
                    <p class="text-xs theme-muted mt-1">Apply promo code <code class="px-1.5 py-0.5 rounded bg-orange-600 text-white font-mono">MEGA20</code> at checkout.</p>
                </div>
                <a href="#marketplace-catalogue" class="mt-4 inline-flex items-center text-xs font-bold text-orange-500 hover:underline">
                    Shop Eligible Items <i class="fa-solid fa-chevron-right ml-1"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- 2. CATEGORY BAR -->
    @if($categories->count())
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-indigo-500"></i> Shop by Department
            </h2>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-indigo-500 hover:underline">Reset Filters</a>
        </div>
        <div class="flex items-center gap-3 overflow-x-auto pb-2">
            @foreach($categories as $cat)
                <a href="{{ route('products.index', array_merge(request()->query(), ['category' => $cat->slug])) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl theme-card shrink-0 hover:border-indigo-500 transition {{ request('category') === $cat->slug ? 'ring-2 ring-indigo-600' : '' }}">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center">
                        <i class="fa-solid {{ $cat->icon }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold">{{ $cat->name }}</div>
                        <div class="text-[10px] theme-muted">{{ $cat->products_count }} items</div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- 3. LIGHTNING DEALS SECTION -->
    @if($flashDeals->count())
    <section class="theme-card rounded-2xl p-6 border-2 border-orange-600">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-orange-600 text-white flex items-center justify-center text-lg">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                <div>
                    <h2 class="text-xl font-extrabold">Lightning Deals</h2>
                    <p class="text-xs theme-muted">Limited-quantity discounts refreshing daily</p>
                </div>
            </div>
            <div x-data="{ hours: 5, minutes: 42, seconds: 19, init() { setInterval(() => { if(this.seconds > 0) this.seconds--; else { this.seconds = 59; if(this.minutes > 0) this.minutes--; } }, 1000) } }"
                 class="flex items-center gap-2 text-xs font-extrabold">
                <span class="theme-muted">ENDS IN:</span>
                <span class="px-2.5 py-1 rounded bg-orange-600 text-white" x-text="String(hours).padStart(2, '0') + 'h'"></span> :
                <span class="px-2.5 py-1 rounded bg-orange-600 text-white" x-text="String(minutes).padStart(2, '0') + 'm'"></span> :
                <span class="px-2.5 py-1 rounded bg-orange-600 text-white" x-text="String(seconds).padStart(2, '0') + 's'"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($flashDeals as $deal)
                <div class="theme-elevated rounded-xl p-4 flex flex-col justify-between relative">
                    @if($deal->discount_percentage > 0)
                        <span class="absolute top-3 left-3 z-10 px-2.5 py-1 rounded text-[10px] font-extrabold bg-rose-600 text-white">
                            -{{ $deal->discount_percentage }}% OFF
                        </span>
                    @endif
                    <a href="{{ route('products.show', $deal) }}" class="block">
                        <div class="h-40 rounded-lg overflow-hidden mb-3 flex items-center justify-center bg-slate-800/10">
                            @if($deal->image_url)
                                <img src="{{ $deal->image_url }}" alt="{{ $deal->name }}" class="h-full w-full object-cover">
                            @else
                                <i class="fa-solid fa-box-open text-4xl text-indigo-500"></i>
                            @endif
                        </div>
                        <h3 class="font-bold text-sm line-clamp-1">{{ $deal->name }}</h3>
                    </a>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-extrabold text-orange-500">${{ number_format($deal->price, 2) }}</span>
                            @if($deal->compare_at_price)
                                <span class="text-xs line-through theme-muted">${{ number_format($deal->compare_at_price, 2) }}</span>
                            @endif
                        </div>
                        <div class="w-full bg-slate-500/20 h-2 rounded-full overflow-hidden">
                            <div class="bg-orange-600 h-full rounded-full" style="width: {{ max(15, min(92, 100 - ($deal->stock * 2))) }}%"></div>
                        </div>
                        <div class="text-[11px] font-bold text-orange-500">{{ $deal->stock }} units remaining</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- 4. MAIN FACETED SEARCH & PRODUCT CATALOGUE -->
    <section id="marketplace-catalogue" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Filter Sidebar -->
        <aside class="lg:col-span-3">
            <form method="GET" action="{{ route('products.index') }}" class="theme-card rounded-2xl p-6 space-y-5 sticky top-24">
                <div class="flex items-center justify-between border-b pb-3" style="border-color: var(--border-color);">
                    <h3 class="font-extrabold text-sm flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-indigo-500"></i> Smart Filters
                    </h3>
                    <a href="{{ route('products.index') }}" class="text-xs text-rose-500 font-bold hover:underline">Clear All</a>
                </div>

                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="category" value="{{ request('category') }}">

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider theme-muted">Price Range ($)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full px-3 py-2 rounded-lg text-xs">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full px-3 py-2 rounded-lg text-xs">
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer">
                        <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) class="rounded text-indigo-600">
                        <span>In Stock Only</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer">
                        <input type="checkbox" name="flash_deals" value="1" @checked(request()->boolean('flash_deals')) class="rounded text-orange-600">
                        <span>Lightning / Discounted Deals</span>
                    </label>
                </div>

                <div class="space-y-2 pt-2">
                    <label class="text-xs font-bold uppercase tracking-wider theme-muted">Sort By</label>
                    <select name="sort" class="w-full px-3 py-2.5 rounded-lg text-xs font-semibold">
                        <option value="featured" @selected(request('sort') === 'featured')>Featured & Recommended</option>
                        <option value="popular" @selected(request('sort') === 'popular')>Best Selling Orders</option>
                        <option value="rating" @selected(request('sort') === 'rating')>Highest Customer Rating</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="newest" @selected(request('sort') === 'newest')>Newest Arrivals</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition">
                    Apply Filters
                </button>
            </form>
        </aside>

        <!-- Right Product Grid -->
        <div class="lg:col-span-9 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 theme-card rounded-xl px-5 py-3">
                <div class="text-xs font-semibold">
                    Showing <span class="font-extrabold text-indigo-500">{{ $products->total() }}</span> products
                    @if(request('q')) for <span class="italic">"{{ request('q') }}"</span> @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($products as $product)
                    <div class="theme-card rounded-2xl p-5 flex flex-col justify-between relative hover:border-indigo-500 transition">
                        <!-- Top Badges & Customer-Only AJAX Wishlist Button -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-indigo-600 text-white">
                                {{ $product->category->name ?? 'General' }}
                            </span>
                            @auth
                                @if(auth()->user()->role === 'customer')
                                    <form method="POST" action="{{ route('products.wishlist', $product) }}" onsubmit="window.toggleWishlistAjax(event, this)">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 rounded-full theme-elevated flex items-center justify-center hover:text-rose-500 transition" title="Toggle Wishlist">
                                            <i class="wishlist-icon fa-{{ in_array($product->id, $wishlistProductIds ?? []) ? 'solid text-rose-500' : 'regular' }} fa-heart"></i>
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>

                        <!-- Product Image -->
                        <a href="{{ route('products.show', $product) }}" class="block my-2">
                            <div class="h-48 rounded-xl overflow-hidden flex items-center justify-center bg-slate-800/10">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <i class="fa-solid fa-box-open text-5xl text-indigo-500"></i>
                                @endif
                            </div>
                        </a>

                        <!-- Product Details -->
                        <div class="space-y-2 mt-2">
                            <div class="flex items-center justify-between text-[11px] theme-muted">
                                <span><i class="fa-solid fa-store mr-1 text-indigo-500"></i>{{ $product->vendor->store_name ?? $product->vendor->name ?? 'Official Store' }}</span>
                                <span class="text-amber-500 font-bold">
                                    <i class="fa-solid fa-star"></i> {{ number_format($product->average_rating, 1) }}
                                    <span class="theme-muted">({{ $product->reviews_count ?? 0 }})</span>
                                </span>
                            </div>

                            <a href="{{ route('products.show', $product) }}" class="block font-extrabold text-base hover:text-indigo-500 transition line-clamp-1">
                                {{ $product->name }}
                            </a>

                            <p class="text-xs theme-muted line-clamp-2">{{ $product->description }}</p>

                            <div class="pt-3 flex items-center justify-between border-t" style="border-color: var(--border-color);">
                                <div>
                                    <span class="text-xl font-extrabold text-indigo-500">${{ number_format($product->price, 2) }}</span>
                                    @if($product->compare_at_price)
                                        <span class="text-xs line-through theme-muted ml-1">${{ number_format($product->compare_at_price, 2) }}</span>
                                    @endif
                                </div>

                                @if($product->stock > 0)
                                    @guest
                                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                                            <i class="fa-solid fa-cart-plus mr-1"></i> Add
                                        </a>
                                    @else
                                        @if(auth()->user()->role === 'customer')
                                            <form method="POST" action="{{ route('cart.add', $product) }}" onsubmit="window.addToCartAjax(event, this)">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                                                    <i class="fa-solid fa-cart-plus mr-1"></i> Add
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-2.5 py-1 rounded text-[10px] font-bold theme-elevated theme-muted">
                                                Customer Only
                                            </span>
                                        @endif
                                    @endguest
                                @else
                                    <span class="px-3 py-1 rounded-xl bg-rose-600 text-white text-xs font-bold">Sold Out</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full theme-card rounded-2xl p-12 text-center space-y-3">
                        <i class="fa-solid fa-box-open text-4xl text-indigo-500"></i>
                        <h3 class="text-lg font-bold">No matching products found</h3>
                        <p class="text-xs theme-muted">Try adjusting your price range or clearing your search filters.</p>
                    </div>
                @endforelse
            </div>

            <div class="pt-6">
                {{ $products->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
