@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12" x-data="{ compareList: [] }">

    <!-- 1. 3D HERO BENTO GRID (Amazon / AliExpress Style) -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 reveal-on-scroll">
        <!-- Main 3D Hero Banner -->
        <div data-tilt class="lg:col-span-8 card-3d relative rounded-3xl overflow-hidden p-8 md:p-12 bg-gradient-to-br from-indigo-900 via-slate-900 to-purple-950 text-white shadow-2xl border border-indigo-500/30 flex flex-col justify-between min-h-[360px]">
            <div class="relative z-10 max-w-xl space-y-4 card-3d-floating">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/20 text-orange-300 border border-orange-400/30">
                    <i class="fa-solid fa-fire-flame-curved"></i> GLOBAL SUPER DEALS FESTIVAL
                </span>
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight">
                    Next-Gen Shopping in <span class="bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">3D & Real-Time</span>
                </h1>
                <p class="text-slate-300 text-sm md:text-base">
                    Direct from verified manufacturers & top brands. Instant JWT-secured checkout, verified buyer reviews, and live package tracking.
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="#marketplace-catalogue" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-orange-500 to-pink-600 font-bold text-sm shadow-lg hover:scale-105 transition">
                        Explore Deals <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                    <a href="?flash_deals=1#marketplace-catalogue" class="px-6 py-3 rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur font-bold text-sm transition">
                        <i class="fa-solid fa-bolt text-yellow-300 mr-1"></i> Lightning Offers
                    </a>
                </div>
            </div>
            <!-- Decorative 3D Floating Orb -->
            <div class="absolute -right-12 -bottom-12 w-72 h-72 rounded-full bg-gradient-to-tr from-indigo-500/40 to-orange-500/40 blur-3xl pointer-events-none"></div>
        </div>

        <!-- Side Promotional Bento Cards -->
        <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-6">
            <div data-tilt class="card-3d glass-panel rounded-3xl p-6 flex flex-col justify-between bg-gradient-to-br from-emerald-500/10 to-teal-500/5">
                <div class="card-3d-floating">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Verified Sellers</span>
                    <h3 class="text-xl font-extrabold mt-1">Become a Global Vendor</h3>
                    <p class="text-xs opacity-75 mt-1">Instant account approval, real-time 3D sales analytics & automated payouts.</p>
                </div>
                <a href="{{ route('register') }}" class="mt-4 inline-flex items-center text-xs font-bold text-emerald-400 hover:underline">
                    Open Your Storefront <i class="fa-solid fa-chevron-right ml-1"></i>
                </a>
            </div>

            <div data-tilt class="card-3d glass-panel rounded-3xl p-6 flex flex-col justify-between bg-gradient-to-br from-orange-500/10 to-rose-500/5">
                <div class="card-3d-floating">
                    <span class="text-xs font-bold uppercase tracking-wider text-orange-400">Voucher Center</span>
                    <h3 class="text-xl font-extrabold mt-1">Extra $25 Off Orders $150+</h3>
                    <p class="text-xs opacity-75 mt-1">Apply code <code class="px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-300 font-mono">MEGA20</code> at checkout.</p>
                </div>
                <a href="#marketplace-catalogue" class="mt-4 inline-flex items-center text-xs font-bold text-orange-400 hover:underline">
                    Shop Eligible Items <i class="fa-solid fa-chevron-right ml-1"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- 2. INTERACTIVE CATEGORY CAROUSEL -->
    @if($categories->count())
    <section class="reveal-on-scroll space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-indigo-500"></i> Shop by Department
            </h2>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-indigo-400 hover:underline">Reset Filters</a>
        </div>
        <div class="flex items-center gap-3 overflow-x-auto pb-2">
            @foreach($categories as $cat)
                <a href="{{ route('products.index', array_merge(request()->query(), ['category' => $cat->slug])) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl glass-panel shrink-0 hover:border-indigo-500 transition {{ request('category') === $cat->slug ? 'ring-2 ring-indigo-500 bg-indigo-500/10' : '' }}">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                        <i class="fa-solid {{ $cat->icon }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold">{{ $cat->name }}</div>
                        <div class="text-[10px] opacity-60">{{ $cat->products_count }} items</div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- 3. FLASH DEALS / LIGHTNING DEALS WITH LIVE COUNTDOWN -->
    @if($flashDeals->count())
    <section class="reveal-on-scroll glass-panel rounded-3xl p-6 border-orange-500/30 bg-gradient-to-r from-orange-500/5 via-transparent to-pink-500/5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-lg shadow-lg shadow-orange-500/30">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                <div>
                    <h2 class="text-xl font-extrabold">Lightning Deals</h2>
                    <p class="text-xs opacity-70">Limited-quantity discounts refreshing daily</p>
                </div>
            </div>
            <!-- Live Alpine Countdown Timer -->
            <div x-data="{ hours: 5, minutes: 42, seconds: 19, init() { setInterval(() => { if(this.seconds > 0) this.seconds--; else { this.seconds = 59; if(this.minutes > 0) this.minutes--; } }, 1000) } }"
                 class="flex items-center gap-2 text-xs font-extrabold">
                <span class="opacity-70">ENDS IN:</span>
                <span class="px-2.5 py-1 rounded-lg bg-orange-500 text-white" x-text="String(hours).padStart(2, '0') + 'h'"></span> :
                <span class="px-2.5 py-1 rounded-lg bg-orange-500 text-white" x-text="String(minutes).padStart(2, '0') + 'm'"></span> :
                <span class="px-2.5 py-1 rounded-lg bg-orange-500 text-white" x-text="String(seconds).padStart(2, '0') + 's'"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($flashDeals as $deal)
                <div data-tilt class="card-3d glass-panel rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden group">
                    @if($deal->discount_percentage > 0)
                        <span class="absolute top-3 left-3 z-20 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-600 text-white">
                            -{{ $deal->discount_percentage }}% OFF
                        </span>
                    @endif
                    <a href="{{ route('products.show', $deal) }}" class="block card-3d-floating">
                        <div class="h-40 rounded-xl bg-slate-800/30 flex items-center justify-center overflow-hidden mb-3">
                            @if($deal->image)
                                <img src="{{ asset('storage/' . $deal->image) }}" alt="{{ $deal->name }}" class="h-full w-full object-cover">
                            @else
                                <i class="fa-solid fa-cube text-4xl text-indigo-400"></i>
                            @endif
                        </div>
                        <h3 class="font-bold text-sm line-clamp-1">{{ $deal->name }}</h3>
                    </a>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-extrabold text-orange-400">${{ number_format($deal->price, 2) }}</span>
                            @if($deal->compare_at_price)
                                <span class="text-xs line-through opacity-50">${{ number_format($deal->compare_at_price, 2) }}</span>
                            @endif
                        </div>
                        <!-- Stock Claimed Bar -->
                        <div class="w-full bg-slate-700/30 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-500 to-rose-500 h-full rounded-full" style="width: {{ max(15, min(92, 100 - ($deal->stock * 3))) }}%"></div>
                        </div>
                        <div class="text-[10px] font-semibold text-orange-400">Only {{ $deal->stock }} left in stock!</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- 4. MAIN FACETED SEARCH & 3D PRODUCT CATALOGUE -->
    <section id="marketplace-catalogue" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Faceted Filter Sidebar -->
        <aside class="lg:col-span-3 space-y-6">
            <form method="GET" action="{{ route('products.index') }}" class="glass-panel rounded-3xl p-6 space-y-5 sticky top-24">
                <div class="flex items-center justify-between border-b border-slate-500/20 pb-3">
                    <h3 class="font-extrabold text-sm flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-indigo-500"></i> Smart Filters
                    </h3>
                    <a href="{{ route('products.index') }}" class="text-xs text-rose-400 hover:underline">Clear All</a>
                </div>

                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="category" value="{{ request('category') }}">

                <!-- Price Range -->
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider opacity-70">Price Range ($)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full px-3 py-2 rounded-xl text-xs glass-panel">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full px-3 py-2 rounded-xl text-xs glass-panel">
                    </div>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-2 pt-2">
                    <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer">
                        <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) class="rounded text-indigo-600">
                        <span>In Stock Only</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer">
                        <input type="checkbox" name="flash_deals" value="1" @checked(request()->boolean('flash_deals')) class="rounded text-orange-500">
                        <span>Lightning / Discounted Deals</span>
                    </label>
                </div>

                <!-- Sort Order -->
                <div class="space-y-2 pt-2">
                    <label class="text-xs font-bold uppercase tracking-wider opacity-70">Sort By</label>
                    <select name="sort" class="w-full px-3 py-2.5 rounded-xl text-xs font-semibold glass-panel bg-transparent">
                        <option value="featured" @selected(request('sort') === 'featured')>Featured & Recommended</option>
                        <option value="popular" @selected(request('sort') === 'popular')>Best Selling Orders</option>
                        <option value="rating" @selected(request('sort') === 'rating')>Highest Customer Rating</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="newest" @selected(request('sort') === 'newest')>Newest Arrivals</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition">
                    Apply Filters
                </button>
            </form>
        </aside>

        <!-- Right 3D Product Grid -->
        <div class="lg:col-span-9 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 glass-panel rounded-2xl px-5 py-3">
                <div class="text-xs font-semibold">
                    Showing <span class="font-extrabold text-indigo-400">{{ $products->total() }}</span> results
                    @if(request('q')) for <span class="italic">"{{ request('q') }}"</span> @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($products as $product)
                    <div data-tilt class="card-3d reveal-on-scroll glass-panel rounded-3xl p-5 flex flex-col justify-between relative group">
                        <!-- Top Badges & Wishlist Button -->
                        <div class="flex items-center justify-between z-20 mb-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-500/15 text-indigo-400">
                                {{ $product->category->name ?? 'General' }}
                            </span>
                            @auth
                                <form method="POST" action="{{ route('products.wishlist', $product) }}">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 rounded-full glass-panel flex items-center justify-center hover:text-rose-500 transition">
                                        <i class="fa-{{ in_array($product->id, $wishlistProductIds ?? []) ? 'solid text-rose-500' : 'regular' }} fa-heart"></i>
                                    </button>
                                </form>
                            @endauth
                        </div>

                        <!-- 3D Floating Product Image -->
                        <a href="{{ route('products.show', $product) }}" class="block my-2 card-3d-floating">
                            <div class="h-48 rounded-2xl bg-slate-800/20 overflow-hidden flex items-center justify-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-box-open text-5xl text-indigo-400/60"></i>
                                @endif
                            </div>
                        </a>

                        <!-- Product Details -->
                        <div class="space-y-2 mt-2">
                            <div class="flex items-center justify-between text-[11px] opacity-75">
                                <span><i class="fa-solid fa-store mr-1 text-indigo-400"></i>{{ $product->vendor->store_name ?? $product->vendor->name ?? 'Official Store' }}</span>
                                <span class="text-amber-400 font-bold">
                                    <i class="fa-solid fa-star"></i> {{ number_format($product->average_rating, 1) }}
                                    <span class="opacity-60">({{ $product->reviews_count ?? 0 }})</span>
                                </span>
                            </div>

                            <a href="{{ route('products.show', $product) }}" class="block font-extrabold text-base hover:text-indigo-400 transition line-clamp-1">
                                {{ $product->name }}
                            </a>

                            <p class="text-xs opacity-70 line-clamp-2">{{ $product->description }}</p>

                            <div class="pt-3 flex items-center justify-between border-t border-slate-500/15">
                                <div>
                                    <span class="text-xl font-extrabold text-indigo-400">${{ number_format($product->price, 2) }}</span>
                                    @if($product->compare_at_price)
                                        <span class="text-xs line-through opacity-50 ml-1">${{ number_format($product->compare_at_price, 2) }}</span>
                                    @endif
                                </div>

                                @if($product->stock > 0)
                                    <form method="POST" action="{{ route('cart.add', $product) }}">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-orange-500 text-white text-xs font-bold shadow-md transition">
                                            <i class="fa-solid fa-cart-plus mr-1"></i> Add
                                        </button>
                                    </form>
                                @else
                                    <span class="px-3 py-1 rounded-xl bg-rose-500/15 text-rose-400 text-xs font-bold">Sold Out</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full glass-panel rounded-3xl p-12 text-center space-y-3">
                        <i class="fa-solid fa-box-open text-4xl text-indigo-400"></i>
                        <h3 class="text-lg font-bold">No matching products found</h3>
                        <p class="text-xs opacity-70">Try adjusting your price range or clearing your search filters.</p>
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
