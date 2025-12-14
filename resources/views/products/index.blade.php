@extends('layouts.app')

@section('content')
<h1 class="mb-4">Product Catalog</h1>

<div class="row">
    @foreach($products as $product)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            {{-- Placeholder Image --}}
                <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/300x200' }}"
                    class="card-img-top"
                    alt="{{ $product->name }}"
                    style="height: 200px; object-fit: cover;">
                 <div class="card-body">
                <h5 class="card-title">{{ $product->name }}</h5>
                <p class="card-text text-muted">{{ Str::limit($product->description, 50) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="h5 mb-0">${{ $product->price }}</span>
                    @if($product->stock > 0)
                        <span class="badge bg-success">In Stock</span>
                    @else
                        <span class="badge bg-danger">Out of Stock</span>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                <form action="{{ url('/cart/add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-outline-primary w-100" {{ $product->stock == 0 ? 'disabled' : '' }}>
                        Add to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center">
    {{ $products->links() }}
</div>
@endsection
