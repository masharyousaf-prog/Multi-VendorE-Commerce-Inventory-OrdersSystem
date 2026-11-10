@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
        title: 'Flagship Wireless ANC Headphones',
        brand: 'NexaAudio',
        price: 129.99,
        comparePrice: 179.99,
        stock: 25,
        isFlash: true
     }">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-orange-400">Seller Studio</span>
            <h1 class="text-3xl font-extrabold">Publish New Marketplace SKU</h1>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="px-4 py-2 rounded-xl glass-panel text-xs font-bold">Back to Seller Hub</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Form (8 Cols) -->
        <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data" class="lg:col-span-8 glass-panel rounded-3xl p-6 md:p-8 space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="text-xs font-bold block mb-1">Product Title</label>
                    <input type="text" name="name" x-model="title" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Brand Name</label>
                    <input type="text" name="brand" x-model="brand" placeholder="e.g. Sony, Anker" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Department Category</label>
                    <select name="category_id" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel bg-transparent">
                        <option value="">Select Category</option>
                        @foreach($categories ?? \App\Models\Category::all() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Selling Price ($)</label>
                    <input type="number" step="0.01" name="price" x-model="price" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Compare-At / Original Price ($)</label>
                    <input type="number" step="0.01" name="compare_at_price" x-model="comparePrice" class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Available Stock Units</label>
                    <input type="number" name="stock" x-model="stock" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Product Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-4 py-2.5 rounded-2xl text-xs glass-panel">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold block mb-1">Detailed Description</label>
                <textarea name="description" rows="4" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel" placeholder="Highlight key features, warranty, and box contents..."></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" name="is_flash_deal" value="1" x-model="isFlash" class="rounded text-orange-500">
                <span class="text-xs font-bold">Enroll in Homepage Lightning Deals Carousel</span>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-orange-500 to-pink-600 text-white font-extrabold text-xs shadow-lg">
                <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Publish Live to Storefront
            </button>
        </form>

        <!-- Right Live 3D Card Preview (4 Cols) -->
        <div class="lg:col-span-4 space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider opacity-70">Live 3D Storefront Preview</div>
            <div data-tilt class="card-3d glass-panel rounded-3xl p-5 space-y-3 border-orange-500/30">
                <div class="flex justify-between items-center">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-500/15 text-indigo-400" x-text="brand || 'Brand'"></span>
                    <span x-show="isFlash" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-orange-500 text-white">
                        <i class="fa-solid fa-bolt"></i> FLASH DEAL
                    </span>
                </div>
                <div class="h-44 rounded-2xl bg-slate-800/30 flex items-center justify-center card-3d-floating">
                    <i class="fa-solid fa-cube text-5xl text-indigo-400"></i>
                </div>
                <h3 class="font-extrabold text-base" x-text="title || 'Product Title'"></h3>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-extrabold text-indigo-400" x-text="'$' + Number(price || 0).toFixed(2)"></span>
                    <span x-show="comparePrice > price" class="text-xs line-through opacity-50" x-text="'$' + Number(comparePrice).toFixed(2)"></span>
                </div>
                <div class="text-[11px] text-emerald-400 font-semibold" x-text="stock + ' units in stock'"></div>
            </div>
        </div>
    </div>
</div>
@endsection
