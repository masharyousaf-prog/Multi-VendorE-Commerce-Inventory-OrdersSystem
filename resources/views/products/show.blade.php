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
    <nav class="flex items-center gap-2 text-xs font-semibold opacity-75">
        <a href="{{ route('products.index') }}" class="hover:text-indigo-400">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @if($product->category)
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-indigo-400">{{ $product->category->name }}</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @endif
        <span class="text-indigo-400 truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <!-- Primary 3-Column Product Showcase (Gallery + Info + Amazon Buy Box) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Column 1: 3D Interactive Image Magnifier (5 Cols) -->
        <div class="lg:col-span-5">
            <div data-tilt class="card-3d glass-panel rounded-3xl p-4 relative overflow-hidden cursor-zoom-in"
                 @mouseenter="zoom = true"
                 @mouseleave="zoom = false"
                 @mousemove="handleZoom($event)">

                @if($product->discount_percentage > 0)
                    <span class="absolute top-6 left-6 z-20 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-600 text-white shadow-lg">
                        SAVE {{ $product->discount_percentage }}%
                    </span>
                @endif

                <div class="h-[420px] rounded-2xl overflow-hidden bg-slate-900/30 flex items-center justify-center relative">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover transition-transform duration-200"
                             :style="zoom ? `transform: scale(2.1); transform-origin: ${zoomX}% ${zoomY}%` : 'transform: scale(1)'">
                    @else
                        <i class="fa-solid fa-cube text-8xl text-indigo-400/50 card-3d-floating"></i>
                    @endif
                </div>

                <div class="mt-3 flex items-center justify-between text-[11px] opacity-70 px-2">
                    <span><i class="fa-solid fa-magnifying-glass-plus mr-1 text-indigo-400"></i> Hover to magnify 2.1x</span>
                    <span><i class="fa-solid fa-eye mr-1"></i> {{ number_format($product->views_count ?? 1) }} views</span>
                </div>
            </div>
        </div>

        <!-- Column 2: Product Core Metadata & Highlights (4 Cols) -->
        <div class="lg:col-span-4 space-y-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/15 text-indigo-400">
                    {{ $product->brand ?? 'Verified Brand' }}
                </span>
                <span class="text-xs opacity-60 font-mono">SKU: {{ $product->sku ?? 'N/A' }}</span>
            </div>

            <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">{{ $product->name }}</h1>

            <!-- Rating & Vendor Link -->
            <div class="flex flex-wrap items-center gap-4 text-xs border-b border-slate-500/20 pb-4">
                <div class="flex items-center gap-1 text-amber-400 font-bold">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-{{ $i <= round($product->average_rating) ? 'solid' : 'regular' }} fa-star"></i>
                    @endfor
                    <span class="ml-1">{{ number_format($product->average_rating, 1) }}</span>
                </div>
                <span class="opacity-60">|</span>
                <a href="#reviews-section" @click="activeTab = 'reviews'" class="text-indigo-400 hover:underline font-semibold">
                    {{ $product->reviews->count() }} Verified Reviews
                </a>
                <span class="opacity-60">|</span>
                <span>Sold by <strong class="text-emerald-400">{{ $product->vendor->store_name ?? $product->vendor->name ?? 'Official Vendor' }}</strong></span>
            </div>

            <!-- Pricing Block -->
            <div class="p-4 rounded-2xl glass-panel bg-gradient-to-r from-indigo-500/10 to-orange-500/10 space-y-1">
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-extrabold text-indigo-400">${{ number_format($product->price, 2) }}</span>
                    @if($product->compare_at_price)
                        <span class="text-sm line-through opacity-50">${{ number_format($product->compare_at_price, 2) }}</span>
                        <span class="text-xs font-bold text-emerald-400">You save ${{ number_format($product->compare_at_price - $product->price, 2) }}</span>
                    @endif
                </div>
                <p class="text-[11px] opacity-75">Price includes applicable VAT. Instant discount with code <strong>MEGA20</strong>.</p>
            </div>

            <!-- Description -->
            <p class="text-sm opacity-85 leading-relaxed">{{ $product->description }}</p>

            <!-- Trust & Guarantee Badges -->
            <div class="grid grid-cols-3 gap-3 pt-2">
                <div class="p-3 rounded-2xl glass-panel text-center">
                    <i class="fa-solid fa-truck-fast text-indigo-400 text-lg mb-1"></i>
                    <div class="text-[11px] font-bold">Fast Dispatch</div>
                    <div class="text-[10px] opacity-60">24-48 Hours</div>
                </div>
                <div class="p-3 rounded-2xl glass-panel text-center">
                    <i class="fa-solid fa-rotate-left text-emerald-400 text-lg mb-1"></i>
                    <div class="text-[11px] font-bold">Free Returns</div>
                    <div class="text-[10px] opacity-60">Within 30 Days</div>
                </div>
                <div class="p-3 rounded-2xl glass-panel text-center">
                    <i class="fa-solid fa-lock text-orange-400 text-lg mb-1"></i>
                    <div class="text-[11px] font-bold">JWT Secured</div>
                    <div class="text-[10px] opacity-60">Buyer Escrow</div>
                </div>
            </div>
        </div>

        <!-- Column 3: Amazon-Style Buy Box (3 Cols) -->
        <div class="lg:col-span-3">
            <div class="glass-panel rounded-3xl p-6 space-y-5 sticky top-24 border-indigo-500/30">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase opacity-70">Availability</span>
                    @if($product->stock > 0)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400">
                            <i class="fa-solid fa-check mr-1"></i> In Stock ({{ $product->stock }} left)
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/15 text-rose-400">Out of Stock</span>
                    @endif
                </div>

                @if($product->stock > 0)
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-xs font-bold block mb-2">Select Quantity</label>
                            <div class="flex items-center rounded-2xl glass-panel overflow-hidden w-full">
                                <button type="button" @click="if(qty > 1) qty--" class="px-4 py-2.5 hover:bg-indigo-500/20 font-bold">-</button>
                                <input type="number" name="quantity" x-model="qty" min="1" max="{{ $product->stock }}" class="w-full text-center bg-transparent font-extrabold text-sm focus:outline-none">
                                <button type="button" @click="if(qty < {{ $product->stock }}) qty++" class="px-4 py-2.5 hover:bg-indigo-500/20 font-bold">+</button>
                            </div>
                        </div>

                        <div class="text-xs flex justify-between opacity-80">
                            <span>Subtotal:</span>
                            <span class="font-extrabold text-indigo-400" x-text="'$' + (qty * {{ (float)$product->price }}).toFixed(2)"></span>
                        </div>

                        <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-extrabold text-xs shadow-lg shadow-indigo-600/30 transition">
                            <i class="fa-solid fa-cart-plus mr-1.5"></i> Add to Shopping Cart
                        </button>
                    </form>
                @endif

                @auth
                    <form method="POST" action="{{ route('products.wishlist', $product) }}">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-2xl glass-panel hover:border-rose-500 text-xs font-bold transition flex items-center justify-center gap-2">
                            <i class="fa-regular fa-heart text-rose-500"></i> Save to Wishlist
                        </button>
                    </form>
                @endauth

                <div class="text-[11px] space-y-2 pt-3 border-t border-slate-500/20 opacity-75">
                    <div><i class="fa-solid fa-location-dot text-indigo-400 mr-1.5"></i> Ships worldwide via Express Courier</div>
                    <div><i class="fa-solid fa-Box text-emerald-400 mr-1.5"></i> Dispatched directly from {{ $product->vendor->store_name ?? $product->vendor->name ?? 'Verified Warehouse' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Tabs: Specifications & Customer Reviews -->
    <div id="reviews-section" class="glass-panel rounded-3xl p-6 md:p-8 space-y-6">
        <div class="flex items-center gap-4 border-b border-slate-500/20 pb-4">
            <button @click="activeTab = 'specs'"
                    :class="activeTab === 'specs' ? 'bg-indigo-600 text-white shadow-lg' : 'glass-panel opacity-75'"
                    class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition">
                <i class="fa-solid fa-microchip mr-1.5"></i> Technical Specifications
            </button>
            <button @click="activeTab = 'reviews'"
                    :class="activeTab === 'reviews' ? 'bg-indigo-600 text-white shadow-lg' : 'glass-panel opacity-75'"
                    class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition">
                <i class="fa-solid fa-star mr-1.5"></i> Verified Customer Reviews ({{ $product->reviews->count() }})
            </button>
        </div>

        <!-- Tab 1: Interactive Technical Specification Table -->
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
                <div class="flex items-center justify-between p-3.5 rounded-2xl glass-panel text-xs">
                    <span class="font-bold opacity-70">{{ $key }}</span>
                    <span class="font-extrabold text-indigo-400">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                </div>
            @endforeach
        </div>

        <!-- Tab 2: Customer Reviews & Rating Submission -->
        <div x-show="activeTab === 'reviews'" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left: Review Submission Form -->
            <div class="lg:col-span-5">
                @auth
                    <form method="POST" action="{{ route('products.reviews.store', $product) }}" class="glass-panel rounded-2xl p-6 space-y-4">
                        @csrf
                        <h3 class="font-extrabold text-base">Write a Customer Review</h3>
                        <div x-data="{ rating: 5 }">
                            <label class="text-xs font-bold block mb-1">Your Rating</label>
                            <input type="hidden" name="rating" :value="rating">
                            <div class="flex items-center gap-2 text-2xl text-amber-400 cursor-pointer">
                                <template x-for="star in 5">
                                    <i @click="rating = star" :class="star <= rating ? 'fa-solid fa-star' : 'fa-regular fa-star'"></i>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Review Headline</label>
                            <input type="text" name="title" required placeholder="Summarize your experience..." class="w-full px-3.5 py-2.5 rounded-xl text-xs glass-panel">
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Detailed Feedback</label>
                            <textarea name="comment" rows="3" required placeholder="What did you like or dislike about this product?" class="w-full px-3.5 py-2.5 rounded-xl text-xs glass-panel"></textarea>
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition">
                            Submit Verified Review
                        </button>
                    </form>
                @else
                    <div class="glass-panel rounded-2xl p-6 text-center space-y-3">
                        <i class="fa-solid fa-user-Astronaut text-3xl text-indigo-400"></i>
                        <h4 class="font-bold text-sm">Sign in to review this product</h4>
                        <a href="{{ route('login') }}" class="inline-block px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold">Sign In Now</a>
                    </div>
                @endauth
            </div>

            <!-- Right: Published Reviews List -->
            <div class="lg:col-span-7 space-y-4">
                @forelse($product->reviews as $review)
                    <div class="glass-panel rounded-2xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-xs">{{ $review->user->name ?? 'Customer' }}</span>
                                @if($review->is_verified_purchase)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Verified Purchase
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] opacity-60">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-amber-400 text-xs">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                            @endfor
                            <strong class="ml-2 text-current">{{ $review->title }}</strong>
                        </div>
                        <p class="text-xs opacity-80 leading-relaxed">{{ $review->comment }}</p>
                    </div>
                @empty
                    <div class="glass-panel rounded-2xl p-8 text-center text-xs opacity-70">
                        No customer reviews yet. Be the first to share your thoughts!
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Related Products Carousel -->
    @if($relatedProducts->count())
        <section class="space-y-4">
            <h2 class="text-xl font-extrabold">Customers Also Viewed</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $rel)
                    <a href="{{ route('products.show', $rel) }}" data-tilt class="card-3d glass-panel rounded-2xl p-4 block">
                        <div class="h-36 rounded-xl bg-slate-800/30 flex items-center justify-center overflow-hidden mb-3 card-3d-floating">
                            @if($rel->image)
                                <img src="{{ asset('storage/' . $rel->image) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-box text-3xl text-indigo-400"></i>
                            @endif
                        </div>
                        <h4 class="font-bold text-sm truncate">{{ $rel->name }}</h4>
                        <div class="text-indigo-400 font-extrabold text-sm mt-1">${{ number_format($rel->price, 2) }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
