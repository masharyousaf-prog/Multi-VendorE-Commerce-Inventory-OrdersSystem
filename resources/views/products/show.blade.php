@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        {{-- Product Image Section --}}
        <div class="col-md-6 mb-4">
            {{-- 🖼️ SMART IMAGE LOGIC START --}}
            @php
                $imageUrl = 'https://via.placeholder.com/400?text=No+Image';

                if ($product->image) {
                    if (str_starts_with($product->image, 'http')) {
                        $imageUrl = $product->image;
                    } else {
                        $imageUrl = asset('storage/' . $product->image);
                    }
                }
            @endphp

            <img src="{{ $imageUrl }}" class="img-fluid rounded shadow-sm" alt="{{ $product->name }}">
            {{-- 🖼️ SMART IMAGE LOGIC END --}}
        </div>

        {{-- Product Details Section --}}
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            <h1 class="display-5 fw-bolder">{{ $product->name }}</h1>
            <div class="fs-5 mb-3">
                <span class="fw-bold text-primary">${{ number_format($product->price, 2) }}</span>
                <span class="mx-2">|</span>
                @if($product->stock > 0)
                    <span class="badge bg-success">In Stock: {{ $product->stock }}</span>
                @else
                    <span class="badge bg-danger">Out of Stock</span>
                @endif
            </div>

            <p class="lead">{{ $product->description }}</p>

            <hr>

            {{-- Add to Cart Form --}}
            <form action="{{ url('/cart/add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="row align-items-center mb-4">
                    <div class="col-auto">
                        <label for="quantity" class="col-form-label">Quantity:</label>
                    </div>
                    <div class="col-auto">
                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="{{ $product->stock }}" style="width: 80px;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-dark"
                            {{-- DISABLE IF: Out of Stock OR Guest OR Admin/Vendor --}}
                            {{ ($product->stock == 0 || !Auth::check() || (Auth::user()->role == 'admin' || Auth::user()->role == 'vendor')) ? 'disabled' : '' }}>

                            @if(!Auth::check())
                                Login to Buy
                            @elseif(Auth::user()->role == 'admin' || Auth::user()->role == 'vendor')
                                Customer Only
                            @elseif($product->stock == 0)
                                Out of Stock
                            @else
                                Add to Cart
                            @endif
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-4">
                <p class="text-muted small">Vendor: <strong>{{ $product->vendor->name ?? 'Shop Official' }}</strong></p>
            </div>
        </div>
    </div>
</div>
@endsection
