@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12"
     x-data="{
        activeTab: 'specs',
        qty: 1,
        zoom: false,
        zoomX: 50,
        zoomY: 50,
        handleZoom(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            this.zoomX = ((e.clientX - rect.left) / rect.width) * 100;
            this.zoomY = ((e.clientY - rect.top) / rect.height) * 100;
        }
     }">

    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs font-semibold theme-muted">
        <a href="{{ route('products.index') }}" class="hover:text-indigo-500">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @if($product->category)
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-indigo-500">{{ $product->category->name }}</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @endif
        <span class="text-indigo-500 font-bold truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <!-- Primary 3-Column Product Showcase -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Column 1: Image Magnifier (5 Cols) -->
        <div class="lg:col-span-5">
            <div class="theme-card rounded-2xl p-4 relative overflow-hidden cursor-zoom-in"
                 @mouseenter="zoom = true"
                 @mouseleave="zoom = false"
                 @mousemove="handleZoom($event)">

                @if($product->discount_percentage > 0)
                    <span class="absolute top-6 left-6 z-20 px-3 py-1 rounded text-xs font-extrabold bg-rose-600 text-white">
                        SAVE {{ $product->discount_percentage }}%
                    </span>
                @endif

                <div class="h-[400px] rounded-xl overflow-hidden bg-slate-800/10 flex items-center justify-center relative">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover transition-transform duration-200"
                             :style="zoom ? `transform: scale(2.0); transform-origin: ${zoomX}% ${zoomY}%` : 'transform: scale(1)'">
                    @else
                        <i class="fa-solid fa-box-open text-8xl text-indigo-500"></i>
                    @endif
                </div>

                <div class="mt-3 flex items-center justify-between text-[11px] theme-muted px-2">
                    <span><i class="fa-solid fa-magnifying-glass-plus mr-1 text-indigo-500"></i> Hover to magnify 2x</span>
                    <span><i class="fa-solid fa-eye mr-1"></i> {{ number_format($product->views_count ?? 1) }} views</span>
                </div>
            </div>
        </div>

        <!-- Column 2: Product Metadata (4 Cols) -->
        <div class="lg:col-span-4 space-y-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 rounded text-xs font-bold bg-indigo-600 text-white">
                    {{ $product->brand ?? 'Verified Brand' }}
                </span>
                <span class="text-xs theme-muted font-mono">SKU: {{ $product->sku ?? 'N/A' }}</span>
            </div>

            <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">{{ $product->name }}</h1>

            <div class="flex flex-wrap items-center gap-4 text-xs border-b pb-4" style="border-color: var(--border-color);">
                <div class="flex items-center gap-1 text-amber-500 font-bold">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-{{ $i <= round($product->average_rating) ? 'solid' : 'regular' }} fa-star"></i>
                    @endfor
                    <span class="ml-1">{{ number_format($product->average_rating, 1) }}</span>
                </div>
                <span class="theme-muted">|</span>
                <a href="#reviews-section" @click="activeTab = 'reviews'" class="text-indigo-500 hover:underline font-semibold">
                    {{ $product->reviews->count() }} Verified Reviews
                </a>
                <span class="theme-muted">|</span>
                <span>Sold by <strong class="text-emerald-500">{{ $product->vendor->store_name ?? $product->vendor->name ?? 'Official Vendor' }}</strong></span>
            </div>

            <!-- Pricing Block -->
            <div class="p-4 rounded-xl theme-elevated space-y-1">
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-extrabold text-indigo-500">${{ number_format($product->price, 2) }}</span>
                    @if($product->compare_at_price)
                        <span class="text-sm line-through theme-muted">${{ number_format($product->compare_at_price, 2) }}</span>
                        <span class="text-xs font-bold text-emerald-500">Save ${{ number_format($product->compare_at_price - $product->price, 2) }}</span>
                    @endif
                </div>
                <p class="text-[11px] theme-muted">Instant 20% discount available with promo code <strong>MEGA20</strong>.</p>
            </div>

            <p class="text-sm leading-relaxed">{{ $product->description }}</p>

            <div class="grid grid-cols-3 gap-3 pt-2">
                <div class="p-3 rounded-xl theme-card text-center">
                    <i class="fa-solid fa-truck-fast text-indigo-500 text-lg mb-1"></i>
                    <div class="text-[11px] font-bold">Fast Dispatch</div>
                    <div class="text-[10px] theme-muted">24-48 Hours</div>
                </div>
                <div class="p-3 rounded-xl theme-card text-center">
                    <i class="fa-solid fa-rotate-left text-emerald-500 text-lg mb-1"></i>
                    <div class="text-[11px] font-bold">Free Returns</div>
                    <div class="text-[10px] theme-muted">30 Days</div>
                </div>
                <div class="p-3 rounded-xl theme-card text-center">
                    <i class="fa-solid fa-shield-halved text-orange-500 text-lg mb-1"></i>
                    <div class="text-[11px] font-bold">Buyer Escrow</div>
                    <div class="text-[10px] theme-muted">100% Protected</div>
                </div>
            </div>
        </div>

        <!-- Column 3: Buy Box (3 Cols - Customer Only) -->
        <div class="lg:col-span-3">
            <div class="theme-card rounded-2xl p-6 space-y-5 sticky top-24">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase theme-muted">Availability</span>
                    @if($product->stock > 0)
                        <span class="px-2.5 py-1 rounded text-xs font-bold bg-emerald-600 text-white">
                            In Stock ({{ $product->stock }})
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded text-xs font-bold bg-rose-600 text-white">Out of Stock</span>
                    @endif
                </div>

                @if($product->stock > 0)
                    @guest
                        <div class="space-y-3">
                            <a href="{{ route('login') }}" class="block w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-center font-extrabold text-xs transition">
                                <i class="fa-solid fa-cart-plus mr-1.5"></i> Sign In to Purchase
                            </a>
                        </div>
                    @else
                        @if(auth()->user()->role === 'customer')
                            <form method="POST" action="{{ route('cart.add', $product) }}" onsubmit="window.addToCartAjax(event, this)" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="text-xs font-bold block mb-2">Select Quantity</label>
                                    <div class="flex items-center rounded-xl overflow-hidden border" style="border-color: var(--border-color);">
                                        <button type="button" @click="if(qty > 1) qty--" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold transition select-none">-</button>
                                        <input type="number"
                                               name="quantity"
                                               x-model="qty"
                                               readonly
                                               tabindex="-1"
                                               onkeydown="return false;"
                                               onwheel="return false;"
                                               class="no-spinners w-full text-center font-extrabold text-sm py-2 border-0 focus:outline-none cursor-default select-none pointer-events-none">
                                        <button type="button" @click="if(qty < {{ $product->stock }}) qty++" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold transition select-none">+</button>
                                    </div>
                                </div>

                                <div class="text-xs flex justify-between">
                                    <span class="theme-muted">Subtotal:</span>
                                    <span class="font-extrabold text-indigo-500" x-text="'$' + (qty * {{ (float)$product->price }}).toFixed(2)"></span>
                                </div>

                                <button type="submit" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs transition">
                                    <i class="fa-solid fa-cart-plus mr-1.5"></i> Add to Shopping Cart
                                </button>
                            </form>

                            <form method="POST" action="{{ route('products.wishlist', $product) }}" onsubmit="window.toggleWishlistAjax(event, this)">
                                @csrf
                                <button type="submit" class="w-full py-3 rounded-xl theme-elevated hover:border-rose-500 text-xs font-bold transition flex items-center justify-center gap-2">
                                    <i class="wishlist-icon fa-{{ ($inWishlist ?? false) ? 'solid text-rose-500' : 'regular' }} fa-heart"></i>
                                    <span class="wishlist-label">{{ ($inWishlist ?? false) ? 'Saved in Wishlist' : 'Save to Wishlist' }}</span>
                                </button>
                            </form>
                        @else
                            <div class="p-4 rounded-xl theme-elevated text-center text-xs theme-muted font-semibold">
                                <i class="fa-solid fa-user-shield text-indigo-500 block text-lg mb-1"></i>
                                Logged in as <strong class="uppercase">{{ auth()->user()->role }}</strong>. Shopping cart and wishlist are reserved for customer accounts.
                            </div>
                        @endif
                    @endguest
                @endif
            </div>
        </div>
    </div>

    <!-- Specifications & Customer Reviews Tabs -->
    <div id="reviews-section" class="theme-card rounded-2xl p-6 md:p-8 space-y-6">
        <div class="flex items-center gap-4 border-b pb-4" style="border-color: var(--border-color);">
            <button type="button" @click="activeTab = 'specs'"
                    :class="activeTab === 'specs' ? 'bg-indigo-600 text-white' : 'theme-elevated'"
                    class="px-5 py-2.5 rounded-xl text-xs font-extrabold transition">
                <i class="fa-solid fa-microchip mr-1.5"></i> Technical Specifications
            </button>
            <button type="button" @click="activeTab = 'reviews'"
                    :class="activeTab === 'reviews' ? 'bg-indigo-600 text-white' : 'theme-elevated'"
                    class="px-5 py-2.5 rounded-xl text-xs font-extrabold transition">
                <i class="fa-solid fa-star mr-1.5"></i> Verified Customer Reviews ({{ $product->reviews->count() }})
            </button>
        </div>

        <div x-show="activeTab === 'specs'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $specs = is_array($product->specifications) && count($product->specifications)
                    ? $product->specifications
                    : [
                        'Brand' => $product->brand ?? 'NexaMart Official',
                        'SKU Model' => $product->sku ?? 'SKU-' . $product->id,
                        'Category' => $product->category->name ?? 'General Merchandise',
                        'Seller Storefront' => $product->vendor->store_name ?? $product->vendor->name ?? 'Verified Partner',
                        'Warranty Coverage' => '12 Months International Protection',
                        'Condition' => 'Brand New in Sealed Box',
                    ];
            @endphp
            @foreach($specs as $key => $val)
                <div class="flex items-center justify-between p-3.5 rounded-xl theme-elevated text-xs">
                    <span class="font-bold theme-muted">{{ $key }}</span>
                    <span class="font-extrabold text-indigo-500">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                </div>
            @endforeach
        </div>

        <div x-show="activeTab === 'reviews'" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-5">
                @auth
                    @if(auth()->user()->role === 'customer')
                        <form method="POST" action="{{ route('products.reviews.store', $product) }}" class="theme-elevated rounded-2xl p-6 space-y-4">
                            @csrf
                            <h3 class="font-extrabold text-base">Write a Customer Review</h3>
                            <div x-data="{ rating: 5 }">
                                <label class="text-xs font-bold block mb-1">Your Rating</label>
                                <input type="hidden" name="rating" :value="rating">
                                <div class="flex items-center gap-2 text-2xl text-amber-500 cursor-pointer">
                                    <template x-for="star in 5">
                                        <i @click="rating = star" :class="star <= rating ? 'fa-solid fa-star' : 'fa-regular fa-star'"></i>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold block mb-1">Review Headline</label>
                                <input type="text" name="title" required placeholder="Summarize your experience..." class="w-full px-3.5 py-2.5 rounded-xl text-xs">
                            </div>
                            <div>
                                <label class="text-xs font-bold block mb-1">Detailed Feedback</label>
                                <textarea name="comment" rows="3" required placeholder="What did you like or dislike about this product?" class="w-full px-3.5 py-2.5 rounded-xl text-xs"></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition">
                                Submit Verified Review
                            </button>
                        </form>
                    @else
                        <div class="theme-elevated rounded-2xl p-6 text-center space-y-2">
                            <i class="fa-solid fa-user-lock text-2xl text-indigo-500"></i>
                            <h4 class="font-bold text-sm">Customer Reviews Only</h4>
                            <p class="text-xs theme-muted">Only customer accounts can post reviews on marketplace products.</p>
                        </div>
                    @endif
                @else
                    <div class="theme-elevated rounded-2xl p-6 text-center space-y-3">
                        <h4 class="font-bold text-sm">Sign in as a customer to review this product</h4>
                        <a href="{{ route('login') }}" class="inline-block px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold">Sign In Now</a>
                    </div>
                @endauth
            </div>

            <div class="lg:col-span-7 space-y-4">
                @forelse($product->reviews as $review)
                    <div class="theme-elevated rounded-2xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-xs">{{ $review->user->name ?? 'Customer' }}</span>
                                @if($review->is_verified_purchase)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">Verified Purchase</span>
                                @endif
                            </div>
                            <span class="text-[10px] theme-muted">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-amber-500 text-xs">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                            @endfor
                            <strong class="ml-2">{{ $review->title }}</strong>
                        </div>
                        <p class="text-xs theme-muted leading-relaxed">{{ $review->comment }}</p>
                    </div>
                @empty
                    <div class="theme-elevated rounded-2xl p-8 text-center text-xs theme-muted">
                        No customer reviews yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
