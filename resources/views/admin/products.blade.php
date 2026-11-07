@extends('layouts.admin')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Products</h2>

    {{-- NEW BUTTON TO VIEW TRASH --}}
    <a href="{{ route('admin.products.trash') }}" class="btn btn-outline-danger">
        <i class="bi bi-trash"></i> View Trash Bin
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Vendor</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        {{-- 🖼️ SMART IMAGE LOGIC START --}}
                        @php
                            $imageUrl = 'https://via.placeholder.com/50?text=No+Img';

                            if ($product->image) {
                                if (str_starts_with($product->image, 'http')) {
                                    $imageUrl = $product->image;
                                } else {
                                    $imageUrl = asset('storage/' . $product->image);
                                }
                            }
                        @endphp

                        <img src="{{ $imageUrl }}"
                             width="50"
                             height="50"
                             class="rounded shadow-sm"
                             style="object-fit: cover;">
                        {{-- 🖼️ SMART IMAGE LOGIC END --}}
                    </td>
                    <td>
                        <strong>{{ $product->name }}</strong><br>
                        <small class="text-muted">ID: {{ $product->id }}</small>
                    </td>
                    <td>
                        {{-- Shows the name of the user who created it --}}
                        <span class="badge bg-info text-dark">{{ $product->vendor->name ?? 'Unknown' }}</span>
                    </td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>
                        @if($product->stock > 0)
                            <span class="text-success">{{ $product->stock }} in stock</span>
                        @else
                            <span class="text-danger">Out of stock</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.products.delete', $product->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-archive"></i> Soft Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination (Kept inside the card for better UI) --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
