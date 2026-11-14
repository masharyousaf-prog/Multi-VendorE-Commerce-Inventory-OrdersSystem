<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager()" :class="theme" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NexaMart Global Marketplace') }}</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind + Alpine + ApexCharts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        /* Solid Color Theme Variables */
        html.light {
            --bg-main: #f1f5f9;
            --bg-card: #ffffff;
            --bg-elevated: #f8fafc;
            --bg-input: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --border-color: #cbd5e1;
            --primary-solid: #4f46e5;
            --primary-hover: #4338ca;
            --accent-solid: #ea580c;
            --scroll-track: #e2e8f0;
            --scroll-thumb: #6366f1;
            --scroll-thumb-hover: #4338ca;
            color-scheme: light;
        }
        html.dark {
            --bg-main: #0b0f19;
            --bg-card: #111827;
            --bg-elevated: #1e293b;
            --bg-input: #1f2937;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --primary-solid: #6366f1;
            --primary-hover: #4f46e5;
            --accent-solid: #f97316;
            --scroll-track: #0f172a;
            --scroll-thumb: #4f46e5;
            --scroll-thumb-hover: #6366f1;
            color-scheme: dark;
        }
        html.cyberpunk {
            --bg-main: #070312;
            --bg-card: #120924;
            --bg-elevated: #1c0f36;
            --bg-input: #190d30;
            --text-main: #f5d0fe;
            --text-muted: #c084fc;
            --border-color: #9333ea;
            --primary-solid: #d946ef;
            --primary-hover: #c026d3;
            --accent-solid: #06b6d4;
            --scroll-track: #120924;
            --scroll-thumb: #d946ef;
            --scroll-thumb-hover: #06b6d4;
            color-scheme: dark;
        }

        /* Theme-Adaptive Custom Scrollbars (Global & Horizontal Carousels) */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--scroll-thumb) var(--scroll-track);
        }
        ::-webkit-scrollbar {
            width: 10px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--scroll-track);
        }
        ::-webkit-scrollbar-thumb {
            background-color: var(--scroll-thumb);
            border-radius: 9999px;
            border: 2px solid var(--scroll-track);
        }
        ::-webkit-scrollbar-thumb:hover {
            background-color: var(--scroll-thumb-hover);
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            transition: background-color 0.2s ease, color 0.2s ease;
            overflow-x: hidden;
        }

        .glass-panel, .theme-card {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .theme-elevated {
            background-color: var(--bg-elevated);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .theme-muted {
            color: var(--text-muted);
        }

        input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
        select,
        textarea {
            background-color: var(--bg-input) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
        }

        input::placeholder,
        textarea::placeholder {
            color: var(--text-muted) !important;
            opacity: 0.8 !important;
        }

        select option {
            background-color: var(--bg-card) !important;
            color: var(--text-main) !important;
        }

        input.no-spinners::-webkit-outer-spin-button,
        input.no-spinners::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input.no-spinners {
            -moz-appearance: textfield;
        }

        .apexcharts-legend-text {
            color: var(--text-main) !important;
        }
        .apexcharts-xaxis-label, .apexcharts-yaxis-label {
            fill: var(--text-muted) !important;
        }

        /* PRINT / PDF REPORT OVERRIDES: Always force Light Theme & hide Appbar/Footer */
        @media print {
            html, body, html.dark, html.cyberpunk, html.light {
                --bg-main: #ffffff !important;
                --bg-card: #ffffff !important;
                --bg-elevated: #f8fafc !important;
                --bg-input: #ffffff !important;
                --text-main: #0f172a !important;
                --text-muted: #475569 !important;
                --border-color: #cbd5e1 !important;
                background-color: #ffffff !important;
                color: #0f172a !important;
                color-scheme: light !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            #top-announcement-bar,
            #main-app-header,
            #main-app-footer,
            #ajax-toast-container,
            .no-print {
                display: none !important;
            }
            .theme-card, .glass-panel, .theme-elevated {
                background-color: #ffffff !important;
                color: #0f172a !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
            }
            table, th, td {
                color: #0f172a !important;
                border-color: #cbd5e1 !important;
            }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- Floating AJAX Toast Notification -->
    <div id="ajax-toast-container" class="fixed bottom-5 right-5 z-50 hidden">
        <div id="ajax-toast-box" class="px-5 py-3.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold shadow-xl flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span id="ajax-toast-message">Action completed</span>
        </div>
    </div>

    <!-- Top Solid Announcement Bar -->
    <div id="top-announcement-bar" class="bg-indigo-700 text-white text-xs font-semibold py-2 px-4 border-b border-indigo-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-4">
                <span><i class="fa-solid fa-bolt text-amber-300 mr-1"></i> FLASH SALE: Use code <strong class="underline">MEGA20</strong> for 20% OFF</span>
                <a href="{{ route('orders.track') }}" class="hover:underline flex items-center gap-1 text-amber-300 font-bold">
                    <i class="fa-solid fa-truck-fast"></i> Track Order by ID
                </a>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] opacity-80">Theme:</span>
                <div class="flex items-center gap-1 bg-slate-900 rounded-lg px-2 py-1 border border-slate-700">
                    <button type="button" @click="setTheme('light')" :class="theme === 'light' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-300'" class="px-2 py-0.5 rounded text-[11px] transition">
                        <i class="fa-solid fa-sun mr-1"></i>Light
                    </button>
                    <button type="button" @click="setTheme('dark')" :class="theme === 'dark' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-300'" class="px-2 py-0.5 rounded text-[11px] transition">
                        <i class="fa-solid fa-moon mr-1"></i>Dark
                    </button>
                    <button type="button" @click="setTheme('cyberpunk')" :class="theme === 'cyberpunk' ? 'bg-fuchsia-600 text-white font-bold' : 'text-slate-300'" class="px-2 py-0.5 rounded text-[11px] transition">
                        <i class="fa-solid fa-microchip mr-1"></i>Cyber
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Sticky Solid Navigation -->
    <header id="main-app-header" class="sticky top-0 z-40 theme-card shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between gap-4">
                <!-- Solid Brand Logo -->
                <a href="{{ route('products.index') }}" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-extrabold text-xl">
                        N
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight text-indigo-500">NexaMart</span>
                        <span class="block text-[10px] uppercase tracking-widest theme-muted -mt-1">Global Marketplace</span>
                    </div>
                </a>

                <!-- Search Bar -->
                <form action="{{ route('products.index') }}" method="GET" class="flex-1 max-w-xl hidden md:flex items-center">
                    <div class="flex w-full rounded-xl overflow-hidden border" style="border-color: var(--border-color); background-color: var(--bg-input);">
                        <select name="category" class="text-xs font-semibold px-3 py-2.5 border-0 border-r focus:outline-none">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\Category::all() as $navCat)
                                <option value="{{ $navCat->slug }}" @selected(request('category') === $navCat->slug)>{{ $navCat->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Search products, brands, SKUs or verified stores..."
                               class="w-full px-4 py-2.5 text-sm border-0 focus:outline-none">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 text-sm font-bold transition">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                <!-- Action Icons & Role-Based Controls -->
                <div class="flex items-center gap-2">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                                <i class="fa-solid fa-chart-pie mr-1"></i> Dashboard
                            </a>
                            <a href="{{ route('admin.orders') }}" class="px-3 py-2 rounded-xl theme-elevated hover:border-indigo-500 text-xs font-bold transition">
                                <i class="fa-solid fa-truck-ramp-box mr-1 text-indigo-500"></i> Orders
                            </a>
                            <a href="{{ route('admin.coupons') }}" class="px-3 py-2 rounded-xl theme-elevated hover:border-orange-500 text-xs font-bold transition">
                                <i class="fa-solid fa-ticket mr-1 text-orange-500"></i> Coupons
                            </a>
                        @elseif(auth()->user()->role === 'vendor')
                            @php
                                $vendorPendingOrdersCount = \App\Models\OrderItem::whereHas('product', function ($q) {
                                    $q->where('vendor_id', auth()->id())->orWhere('user_id', auth()->id());
                                })->whereIn('status', ['pending', 'processing'])->count();
                            @endphp
                            <a href="{{ route('vendor.dashboard') }}" class="px-3 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold transition">
                                <i class="fa-solid fa-chart-line mr-1"></i> Analytics
                            </a>
                            <a href="{{ route('vendor.orders') }}" class="relative px-3 py-2 rounded-xl theme-elevated hover:border-orange-500 text-xs font-bold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-bell text-orange-500"></i>
                                <span>Orders</span>
                                @if($vendorPendingOrdersCount > 0)
                                    <span class="bg-rose-600 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                                        {{ $vendorPendingOrdersCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('vendor.products.index') }}" class="px-3 py-2 rounded-xl theme-elevated hover:border-indigo-500 text-xs font-bold transition">
                                <i class="fa-solid fa-boxes-stacked mr-1 text-indigo-500"></i> Products
                            </a>
                        @elseif(auth()->user()->role === 'customer')
                            @php
                                $navWishlistCount = \App\Models\Wishlist::where('user_id', auth()->id())->count();
                                $navCartCount = \App\Models\CartItem::whereHas('cart', fn($q) => $q->where('user_id', auth()->id()))->sum('quantity');
                            @endphp
                            <a href="{{ route('orders.my') }}" class="px-3 py-2 rounded-xl theme-elevated hover:border-indigo-500 transition flex items-center gap-1.5 text-xs font-bold">
                                <i class="fa-solid fa-box-archive text-emerald-500"></i>
                                <span class="hidden sm:inline">My Orders</span>
                            </a>
                            <a href="{{ route('wishlist.index') }}" class="relative px-3 py-2 rounded-xl theme-elevated hover:border-rose-500 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-heart text-sm text-rose-500"></i>
                                <span class="text-xs font-bold hidden sm:inline">Wishlist</span>
                                <span id="nav-wishlist-count" class="bg-rose-600 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                                    {{ $navWishlistCount }}
                                </span>
                            </a>
                            <a href="{{ route('cart.index') }}" class="relative px-3 py-2 rounded-xl theme-elevated hover:border-indigo-500 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-cart-shopping text-sm text-indigo-500"></i>
                                <span class="text-xs font-bold hidden sm:inline">Cart</span>
                                <span id="nav-cart-count" class="bg-orange-600 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                                    {{ $navCartCount }}
                                </span>
                            </a>
                        @endif

                        <div class="flex items-center gap-2 pl-2 border-l" style="border-color: var(--border-color);">
                            <div class="text-right hidden lg:block">
                                <div class="text-xs font-bold">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] uppercase tracking-wider text-emerald-500 font-bold">
                                    {{ ucfirst(auth()->user()->role) }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs transition" title="Sign Out">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('orders.track') }}" class="px-3 py-2 rounded-xl text-xs font-bold theme-elevated hover:border-indigo-500 transition">
                            <i class="fa-solid fa-magnifying-glass-location mr-1 text-indigo-500"></i> Track Order
                        </a>
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-xs font-bold theme-elevated hover:border-indigo-500 transition">Sign In</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition">Join Free</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full no-print">
            <div class="p-4 rounded-xl bg-emerald-600 text-white flex items-center justify-between text-sm font-bold shadow">
                <span><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full no-print">
            <div class="p-4 rounded-xl bg-rose-600 text-white text-sm font-bold shadow">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ $errors->first() }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer id="main-app-footer" class="theme-card border-t mt-16 py-6 text-center text-xs theme-muted">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap justify-between items-center gap-4">
            <span>&copy; {{ date('Y') }} NexaMart Global Marketplace. All rights reserved.</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('orders.track') }}" class="hover:text-indigo-500 font-bold">Track Your Order</a>
                <span>•</span>
                <span>Cash on Delivery & Buyer Protection</span>
            </div>
        </div>
    </footer>

    <script>
        function themeManager() {
            return {
                theme: localStorage.getItem('nexamart_theme') || 'dark',
                setTheme(val) {
                    this.theme = val;
                    localStorage.setItem('nexamart_theme', val);
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: val }));
                }
            }
        }

        window.getChartThemeConfig = function() {
            const currentTheme = localStorage.getItem('nexamart_theme') || 'dark';
            const isLight = currentTheme === 'light';
            return {
                mode: isLight ? 'light' : 'dark',
                foreColor: isLight ? '#334155' : (currentTheme === 'cyberpunk' ? '#f5d0fe' : '#cbd5e1'),
                gridColor: isLight ? '#e2e8f0' : (currentTheme === 'cyberpunk' ? '#3b0764' : '#1e293b')
            };
        };

        window.showToast = function(message, isError = false) {
            const container = document.getElementById('ajax-toast-container');
            const box = document.getElementById('ajax-toast-box');
            const msg = document.getElementById('ajax-toast-message');
            if (!container || !box || !msg) return;

            msg.textContent = message;
            box.className = 'px-5 py-3.5 rounded-xl text-white text-xs font-extrabold shadow-xl flex items-center gap-2.5 ' + (isError ? 'bg-rose-600' : 'bg-emerald-600');
            container.classList.remove('hidden');

            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => container.classList.add('hidden'), 3000);
        };

        window.addToCartAjax = async function(event, form) {
            event.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Adding...';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(form)
                });
                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    const badge = document.getElementById('nav-cart-count');
                    if (badge && typeof data.cart_count !== 'undefined') {
                        badge.textContent = data.cart_count;
                    }
                    window.showToast(data.message || 'Added to cart!');
                } else {
                    window.showToast(data.message || 'Could not add to cart.', true);
                }
            } catch (e) {
                window.showToast('Network error while adding to cart.', true);
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        };

        window.toggleWishlistAjax = async function(event, form) {
            event.preventDefault();
            const icon = form.querySelector('.wishlist-icon');
            const label = form.querySelector('.wishlist-label');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (response.ok) {
                    const badge = document.getElementById('nav-wishlist-count');
                    if (badge && typeof data.wishlist_count !== 'undefined') {
                        badge.textContent = data.wishlist_count;
                    }
                    if (icon) {
                        if (data.in_wishlist) {
                            icon.classList.remove('fa-regular');
                            icon.classList.add('fa-solid', 'text-rose-500');
                        } else {
                            icon.classList.remove('fa-solid', 'text-rose-500');
                            icon.classList.add('fa-regular');
                        }
                    }
                    if (label) {
                        label.textContent = data.in_wishlist ? 'Saved in Wishlist' : 'Save to Wishlist';
                    }
                    if (form.dataset.removeCardOnUnwish === 'true' && !data.in_wishlist) {
                        const card = form.closest('.wishlist-item-card');
                        if (card) card.remove();
                    }
                    window.showToast(data.message);
                } else {
                    window.showToast(data.message || 'Unable to update wishlist.', true);
                }
            } catch (e) {
                window.showToast('Network error updating wishlist.', true);
            }
        };
    </script>
</body>
</html>
