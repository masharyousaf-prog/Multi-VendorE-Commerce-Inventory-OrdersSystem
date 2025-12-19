@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Product Catalog</h1>

    <form action="{{ url('/') }}" method="GET" class="d-flex">
        <input
            type="text"
            name="search"
            class="form-control me-2"
            placeholder="Search products..."
            value="{{ request('search') }}"
            style="width: 250px;"
        >
        <button class="btn btn-outline-primary" type="submit">Search</button>

        @if(request('search'))
            <a href="{{ url('/') }}" class="btn btn-outline-secondary ms-2">Reset</a>
        @endif
    </form>
</div>

<div class="row">
    @foreach($products as $product)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            {{-- Image --}}
            <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/300x200' }}"
                 class="card-img-top"
                 alt="{{ $product->name }}"
                 style="height: 200px; object-fit: cover;">

            <div class="card-body">
                <h5 class="card-title">{{ $product->name }}</h5>
                <p class="card-text text-muted">{{ Str::limit($product->description, 50) }}</p>

                <div class="d-flex justify-content-between align-items-center mt-3">

                    {{-- START OF NEW DISCOUNT LOGIC --}}
                    <div>
                        @if($product->discount > 0)
                            <span class="h5 mb-0 text-danger fw-bold">${{ $product->final_price }}</span>

                            <small class="text-muted text-decoration-line-through ms-1">${{ $product->price }}</small>

                            <span class="badge bg-danger ms-1">-{{ $product->discount }}%</span>
                        @else
                            <span class="h5 mb-0">${{ $product->price }}</span>
                        @endif
                    </div>
                    {{-- END OF NEW DISCOUNT LOGIC --}}

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
    {{ $products->appends(['search' => request('search')])->links() }}
</div>
@endsection
