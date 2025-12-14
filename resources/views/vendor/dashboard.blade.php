@extends('layouts.vendor')

@section('vendor_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Vendor Dashboard</h2>
    <a href="{{ url('/vendor/products/create') }}" class="btn btn-success">Add New Product</a>
</div>

<div class="alert alert-info">
    <strong>Total Inventory Value:</strong> ${{ number_format($totalStockValue, 2) }}
</div>

<h3>My Products</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($myProducts as $product)
        <tr>
            <td>{{ $product->name }}</td>
            <td>${{ number_format($product->price, 2) }}</td>
            <td>{{ $product->stock }}</td>
            <td>
                <a href="{{ url('/vendor/products/'.$product->id.'/edit') }}" class="btn btn-sm btn-primary">Edit</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">You haven't added any products yet.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="row mb-5">
    <div class="col-md-8 offset-md-2"> <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title text-center text-muted mb-3">Inventory Stock Levels</h4>
                <canvas id="myStockChart"></canvas>
            </div>
        </div>
    </div>
</div>


<table class="table table-bordered">
    </table>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('myStockChart').getContext('2d');

    const productNames = @json($chartLabels);
    const productStock = @json($chartData);

    new Chart(ctx, {
        type: 'bar', // You can change this to 'pie' or 'line'
        data: {
            labels: productNames,
            datasets: [{
                label: 'Stock Quantity',
                data: productStock,
                backgroundColor: 'rgba(54, 162, 235, 0.6)', // Blue color
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
