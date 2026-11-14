@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
        title: '',
        brand: '',
        sku: '',
        price: '',
        comparePrice: '',
        stock: '',
        description: '',
        imageUrl: '',
        imageBase64: '',
        isFlash: false,
        imagePreview: null,
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const maxDim = 900;
                    let w = img.width, h = img.height;
                    if (w > maxDim || h > maxDim) {
                        if (w > h) { h = Math.round(h * (maxDim / w)); w = maxDim; }
                        else { w = Math.round(w * (maxDim / h)); h = maxDim; }
                    }
                    canvas.width = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    this.imagePreview = dataUrl;
                    this.imageBase64 = dataUrl;
                    // Clear raw file input so PHP upload_max_filesize never blocks submission
                    event.target.value = '';
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
     }">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-orange-500">Seller Studio</span>
            <h1 class="text-3xl font-extrabold">Publish New Marketplace SKU</h1>
        </div>
        <a href="{{ route('vendor.products.index') }}" class="px-4 py-2 rounded-xl theme-card text-xs font-bold">Back to My Products</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Form (8 Cols) -->
        <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data" class="lg:col-span-8 theme-card rounded-2xl p-6 md:p-8 space-y-5">
            @csrf
            <input type="hidden" name="image_base64" :value="imageBase64">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="text-xs font-bold block mb-1">Product Title</label>
                    <input type="text" name="name" x-model="title" placeholder="e.g. Flagship Wireless ANC Headphones" required class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Brand Name</label>
                    <input type="text" name="brand" x-model="brand" placeholder="e.g. Sony, Anker, NexaAudio" class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">SKU Code (Optional)</label>
                    <input type="text" name="sku" x-model="sku" placeholder="Auto-generated if left blank" class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Department Category</label>
                    <select name="category_id" class="w-full px-4 py-3 rounded-xl text-xs">
                        <option value="">Select Category</option>
                        @foreach($categories ?? \App\Models\Category::all() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Available Stock Units</label>
                    <input type="number" name="stock" x-model="stock" placeholder="e.g. 25" required class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Selling Price ($)</label>
                    <input type="number" step="0.01" name="price" x-model="price" placeholder="e.g. 129.99" required class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Compare-At / Original Price ($)</label>
                    <input type="number" step="0.01" name="compare_at_price" x-model="comparePrice" placeholder="e.g. 179.99 (Optional)" class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Upload Product Image</label>
                    <input type="file" accept="image/*" @change="handleFileSelect($event)" class="w-full px-4 py-2.5 rounded-xl text-xs theme-elevated">
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Or Paste Image URL (Optional)</label>
                    <input type="url" name="image_url" x-model="imageUrl" @input="if(imageUrl) imagePreview = imageUrl" placeholder="https://..." class="w-full px-4 py-3 rounded-xl text-xs">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold block mb-1">Detailed Description</label>
                <textarea name="description" x-model="description" rows="4" required class="w-full px-4 py-3 rounded-xl text-xs" placeholder="Highlight key features, warranty, and box contents..."></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" name="is_flash_deal" value="1" x-model="isFlash" class="rounded text-orange-600">
                <span class="text-xs font-bold">Enroll in Homepage Lightning Deals</span>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-xs transition">
                <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Publish Live to Storefront
            </button>
        </form>

        <!-- Right Live Storefront Card Preview (4 Cols) -->
        <div class="lg:col-span-4 space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider theme-muted">Live Storefront Preview</div>
            <div class="theme-card rounded-2xl p-5 space-y-3 border-2 border-indigo-600">
                <div class="flex justify-between items-center">
                    <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-indigo-600 text-white" x-text="brand || 'Brand Hint'"></span>
                    <span x-show="isFlash" class="px-2.5 py-1 rounded text-[10px] font-extrabold bg-orange-600 text-white">
                        <i class="fa-solid fa-bolt"></i> FLASH DEAL
                    </span>
                </div>
                <div class="h-48 rounded-xl bg-slate-800/10 overflow-hidden flex items-center justify-center">
                    <template x-if="imagePreview">
                        <img :src="imagePreview" alt="Live Preview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!imagePreview">
                        <div class="text-center theme-muted text-xs space-y-1">
                            <i class="fa-solid fa-image text-4xl text-indigo-500 block"></i>
                            <span>Select an image or paste URL to preview</span>
                        </div>
                    </template>
                </div>
                <h3 class="font-extrabold text-base line-clamp-1" x-text="title || 'Product Title Preview'"></h3>
                <p class="text-xs theme-muted line-clamp-2" x-text="description || 'Your product description will appear here...'"></p>
                <div class="flex items-baseline gap-2 pt-2 border-t" style="border-color: var(--border-color);">
                    <span class="text-xl font-extrabold text-indigo-500" x-text="'$' + Number(price || 0).toFixed(2)"></span>
                    <span x-show="Number(comparePrice) > Number(price)" class="text-xs line-through theme-muted" x-text="'$' + Number(comparePrice || 0).toFixed(2)"></span>
                </div>
                <div class="text-[11px] text-emerald-500 font-bold" x-text="(stock || 0) + ' units in stock'"></div>
            </div>
        </div>
    </div>
</div>
@endsection
