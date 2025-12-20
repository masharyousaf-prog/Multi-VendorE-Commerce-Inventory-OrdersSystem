@extends('layouts.vendor')

@section('vendor_content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Edit Product</div>
            <div class="card-body">

                <form action="{{ url('/vendor/products/'.$product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') {{-- IMPORTANT: Spoofs a PUT request --}}

                    <div class="mb-3">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                    </div>

                    {{-- 🖼️ Current Image Preview with Smart Logic --}}
                    @if($product->image)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Image:</label><br>

                            @php
                                $imageUrl = '';
                                if (str_starts_with($product->image, 'http')) {
                                    $imageUrl = $product->image;
                                } else {
                                    $imageUrl = asset('storage/' . $product->image);
                                }
                            @endphp

                            <img src="{{ $imageUrl }}"
                                 width="150"
                                 class="rounded border shadow-sm"
                                 alt="Current Product Image">
                        </div>
                    @endif

                    <div class="mb-3">
                        <label>Change Image (Optional)</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="{{ $product->price }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discount Percentage (%)</label>
                        {{-- Added logic to handle null discount value --}}
                        <input type="number" name="discount" class="form-control" min="0" max="100" value="{{ $product->discount ?? 0 }}">
                        <small class="text-muted">Enter 0 for no discount. Example: Enter 20 for 20% Off.</small>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('vendor.dashboard') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
