@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">Edit SKU: {{ $product->name }}</h1>
        <a href="{{ route('vendor.dashboard') }}" class="px-4 py-2 rounded-xl glass-panel text-xs font-bold">Back</a>
    </div>

    <form method="POST" action="{{ url('/vendor/products/' . $product->id) }}" enctype="multipart/form-data" class="glass-panel rounded-3xl p-6 md:p-8 space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="text-xs font-bold block mb-1">Product Title</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Price ($)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Stock Quantity</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">
            </div>
        </div>
        <div>
            <label class="text-xs font-bold block mb-1">Description</label>
            <textarea name="description" rows="4" required class="w-full px-4 py-3 rounded-2xl text-xs glass-panel">{{ old('description', $product->description) }}</textarea>
        </div>
        <button type="submit" class="px-6 py-3 rounded-2xl bg-indigo-600 text-white text-xs font-extrabold">
            Save Product Changes
        </button>
    </form>
</div>
@endsection
