<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager()" :class="theme" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NexaMart Global 3D Marketplace') }}</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind + Alpine + ApexCharts + VanillaTilt 3D -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.1/vanilla-tilt.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        brand: { 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' },
                        accent: { 500: '#f97316', 600: '#ea580c' }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --bg-primary: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.88);
            --text-primary: #0f172a;
            --border-glass: rgba(148, 163, 184, 0.25);
        }
        html.dark {
            --bg-primary: #090d16;
            --bg-card: rgba(17, 24, 39, 0.82);
            --text-primary: #f8fafc;
            --border-glass: rgba(99, 102, 241, 0.22);
        }
        html.cyberpunk {
            --bg-primary: #05010d;
            --bg-card: rgba(20, 6, 45, 0.85);
            --text-primary: #e0e7ff;
            --border-glass: rgba(236, 72, 153, 0.45);
        }
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.35s ease, color 0.35s ease;
            overflow-x: hidden;
        }
        .glass-panel {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-glass);
        }
        /* 3D Card Depth Preservation */
        .card-3d {
            transform-style: preserve-3d;
            perspective: 1100px;
        }
        .card-3d-floating {
            transform: translateZ(36px);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .card-3d:hover .card-3d-floating {
            transform: translateZ(55px) scale(1.06);
        }
        /* Scroll Reveal Animations */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(32px) scale(0.98);
            transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1), transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-on-scroll.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- Top Announcement Bar (AliExpress / Amazon Style) -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-orange-500 text-white text-xs font-semibold py-2 px-4">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-4">
                <span><i class="fa-solid fa-bolt text-yellow-300 mr-1"></i> FLASH SALE: Use code <strong class="underline">MEGA20</strong> for 20% OFF</span>
                <span class="hidden md:inline">| <i class="fa-solid fa-shield-halved mr-1"></i> 100% Buyer Protection & JWT Encrypted Checkout</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Theme Switcher Button -->
                <div class="flex items-center gap-1 bg-black/25 rounded-full px-2.5 py-0.5">
                    <button @click="setTheme('light')" :class="theme === 'light' ? 'text-yellow-300 scale-110' : 'opacity-60'" class="px-1.5 py-0.5 transition" title="Light Mode">
                        <i class="fa-solid fa-sun"></i>
                    </button>
                    <button @click="setTheme('dark')" :class="theme === 'dark' ? 'text-indigo-300 scale-110' : 'opacity-60'" class="px-1.5 py-0.5 transition" title="Dark Mode">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    <button @click="setTheme('cyberpunk')" :class="theme === 'cyberpunk' ? 'text-pink-400 scale-110' : 'opacity-60'" class="px-1.5 py-0.5 transition" title="Cyberpunk 3D Neon">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Sticky Mega Navigation -->
    <header class="sticky top-0 z-50 glass-panel shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between gap-4">
                <!-- Brand Logo -->
                <a href="{{ route('products.index') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-orange-500 flex items-center justify-center text-white font-extrabold text-xl shadow-lg group-hover:rotate-12 transition">
                        N
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-indigo-500 via-purple-500 to-orange-500 bg-clip-text text-transparent">NexaMart</span>
                        <span class="block text-[10px] uppercase tracking-widest opacity-60 -mt-1">Global Multi-Vendor</span>
                    </div>
                </a>

                <!-- Amazon-Grade Omnibox Search Bar -->
                <form action="{{ route('products.index') }}" method="GET" class="flex-1 max-w-2xl hidden md:flex items-center">
                    <div class="flex w-full rounded-2xl overflow-hidden border border-indigo-500/30 focus-within:ring-2 focus-within:ring-indigo-500 bg-white/90 dark:bg-slate-900/90">
                        <select name="category" class="bg-slate-100 dark:bg-slate-800 text-xs font-semibold px-3 border-r border-slate-300 dark:border-slate-700 focus:outline-none">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\Category::all() as $navCat)
                                <option value="{{ $navCat->slug }}" @selected(request('category') === $navCat->slug)>{{ $navCat->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Search millions of products, brands, SKUs or verified stores..."
                               class="w-full px-4 py-2.5 text-sm bg-transparent focus:outline-none">
                        <button type="submit" class="bg-gradient-to-r from-indigo-600 to-orange-500 hover:opacity-95 text-white px-6 text-sm font-bold transition">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                <!-- Action Icons & JWT Auth State -->
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600/15 text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-bold transition">
                                <i class="fa-solid fa-chart-pie mr-1"></i> Admin Command
                            </a>
                        @elseif(auth()->user()->role === 'vendor')
                            <a href="{{ route('vendor.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-orange-500/15 text-orange-400 hover:bg-orange-500 hover:text-white text-xs font-bold transition">
                                <i class="fa-solid fa-store mr-1"></i> Seller Hub
                            </a>
                        @endif

                        <a href="{{ route('cart.index') }}" class="relative p-2.5 rounded-xl glass-panel hover:border-indigo-500 transition">
                            <i class="fa-solid fa-cart-shopping text-lg"></i>
                            <span class="absolute -top-1.5 -right-1.5 bg-orange-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                {{ optional(auth()->user()->cart)->items ? auth()->user()->cart->items->sum('quantity') : 0 }}
                            </span>
                        </a>

                        <div class="flex items-center gap-2 pl-2 border-l border-slate-500/20">
                            <div class="text-right hidden sm:block">
                                <div class="text-xs font-bold">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] uppercase tracking-wider text-emerald-400 font-semibold">
                                    <i class="fa-solid fa-key text-[8px]"></i> JWT: {{ auth()->user()->role }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 rounded-xl bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white text-xs transition" title="Sign Out">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-xs font-bold glass-panel hover:border-indigo-500 transition">Sign In</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-indigo-600 to-orange-500 text-white shadow-md hover:opacity-95 transition">Join Free</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 flex items-center justify-between text-sm font-medium">
                <span><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Global Scripts: Theme Manager, 3D Tilt & Scroll Reveal -->
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

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize 3D Tilt Cards
            if (window.VanillaTilt) {
                VanillaTilt.init(document.querySelectorAll('[data-tilt]'), {
                    max: 10,
                    speed: 400,
                    glare: true,
                    'max-glare': 0.22,
                    scale: 1.02
                });
            }

            // Initialize Scroll Reveal Observer
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
