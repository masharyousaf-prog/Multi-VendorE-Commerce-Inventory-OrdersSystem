@extends('layouts.vendor')

@section('vendor_content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Vendor Dashboard</h2>
        <a href="{{ url('/vendor/products/create') }}" class="btn btn-success">Add New Product</a>
    </div>

    {{-- 🔔 NOTIFICATIONS ALERT --}}
    @if(isset($notifications) && $notifications->count() > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="bi bi-bell-fill"></i> Admin Notifications</h5>
            <ul class="mb-2">
                @foreach($notifications as $notification)
                    <li>
                        {{-- Shows: "Your product 'XYZ' was Permanently Deleted..." --}}
                        {{ $notification->data['message'] }}
                        <span class="text-muted small">({{ $notification->created_at->diffForHumans() }})</span>
                    </li>
                @endforeach
            </ul>

            {{-- Form to Mark as Read (Clears the alert) --}}
            <form action="{{ route('vendor.notifications.read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-dark">Mark all as Read</button>
            </form>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info">
        <strong>Total Inventory Value:</strong> ${{ number_format($totalStockValue, 2) }}
    </div>

    <h3>My Products</h3>
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 80px;">Image</th>
                <th>Product Name</th>
                <th>Price</th> {{-- Updated Column --}}
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($myProducts as $product)
                <tr>
                    {{-- 🖼️ Image Column --}}
                    <td>
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
                             alt="{{ $product->name }}"
                             class="rounded border"
                             style="width: 50px; height: 50px; object-fit: cover;">
                    </td>

                    <td>{{ $product->name }}</td>

                    {{-- 💲 PRICE COLUMN WITH DISCOUNT LOGIC --}}
                    <td>
                        @if($product->discount > 0)
                            <div class="d-flex flex-column">
                                <span class="text-danger fw-bold">
                                    ${{ number_format($product->final_price, 2) }}
                                </span>
                                <small class="text-muted text-decoration-line-through">
                                    ${{ number_format($product->price, 2) }}
                                </small>
                                <span class="badge bg-danger mt-1" style="width: fit-content;">
                                    -{{ $product->discount }}%
                                </span>
                            </div>
                        @else
                            <span class="fw-bold">${{ number_format($product->price, 2) }}</span>
                        @endif
                    </td>

                    <td>
                        @if($product->stock > 0)
                            <span class="text-success">{{ $product->stock }}</span>
                        @else
                            <span class="text-danger fw-bold">Out of Stock</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ url('/vendor/products/' . $product->id . '/edit') }}"
                            class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">You haven't added any products yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Inventory Chart --}}
    <div class="row mb-5 mt-4">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-center text-muted mb-3">Inventory Stock Levels</h4>
                    <canvas id="myStockChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-3 mb-5">
        {{ $myProducts->links() }}
    </div>

    {{-- Chart Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myStockChart').getContext('2d');

        const productNames = @json($chartLabels);
        const productStock = @json($chartData);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: productNames,
                datasets: [{
                    label: 'Stock Quantity',
                    data: productStock,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Units in Stock'
                        }
                    }
                }
            }
        });
    </script>
@endsection
