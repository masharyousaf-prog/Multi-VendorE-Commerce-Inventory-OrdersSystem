@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
        imagePreview: '{{ $product->image_url }}',
        imageBase64: '',
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
                    event.target.value = '';
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
     }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">Edit SKU: {{ $product->name }}</h1>
        <a href="{{ route('vendor.products.index') }}" class="px-4 py-2 rounded-xl theme-card text-xs font-bold">Back to My Products</a>
    </div>

    <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data" class="theme-card rounded-2xl p-6 md:p-8 space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="image_base64" :value="imageBase64">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="text-xs font-bold block mb-1">Product Title</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-3 rounded-xl text-xs">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Brand</label>
                <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="w-full px-4 py-3 rounded-xl text-xs">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Category</label>
                <select name="category_id" class="w-full px-4 py-3 rounded-xl text-xs">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($product->category_id == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Selling Price ($)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full px-4 py-3 rounded-xl text-xs">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Compare-At Price ($)</label>
                <input type="number" step="0.01" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" class="w-full px-4 py-3 rounded-xl text-xs">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Stock Quantity</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required class="w-full px-4 py-3 rounded-xl text-xs">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Replace Product Image</label>
                <input type="file" accept="image/*" @change="handleFileSelect($event)" class="w-full px-4 py-2.5 rounded-xl text-xs theme-elevated">
            </div>
        </div>

        <template x-if="imagePreview">
            <div class="w-32 h-32 rounded-xl overflow-hidden border" style="border-color: var(--border-color);">
                <img :src="imagePreview" class="w-full h-full object-cover">
            </div>
        </template>

        <div>
            <label class="text-xs font-bold block mb-1">Description</label>
            <textarea name="description" rows="4" required class="w-full px-4 py-3 rounded-xl text-xs">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_flash_deal" value="1" @checked($product->is_flash_deal) class="rounded text-orange-600">
            <span class="text-xs font-bold">Enroll in Homepage Lightning Deals</span>
        </div>

        <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold transition">
            Save Product Changes
        </button>
    </form>
</div>
@endsection
