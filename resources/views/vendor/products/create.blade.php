@extends('layouts.vendor')

@section('vendor_content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Create New Product</div>
            <div class="card-body">
                <form action="{{ url('/vendor/products') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discount Percentage (%)</label>
                        <input type="number" name="discount" class="form-control" min="0" max="100" value="0">
                        <small class="text-muted">Enter 0 for no discount. Example: Enter 20 for 20% Off.</small>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
