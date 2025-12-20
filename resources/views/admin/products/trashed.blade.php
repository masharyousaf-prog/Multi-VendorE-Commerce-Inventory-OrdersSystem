@extends('layouts.admin')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-trash"></i> Deleted Products (Trash)</h2>
    <a href="{{ route('admin.products') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Products
    </a>
</div>

<div class="card shadow-sm border-danger">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-danger">
                <tr>
                    <th>Product</th>
                    <th>Vendor</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trashedProducts as $product)
                <tr>
                    <td>
                        <strong>{{ $product->name }}</strong><br>
                        <small class="text-muted">ID: {{ $product->id }}</small>
                    </td>
                    <td>{{ $product->vendor->name ?? 'Unknown' }}</td>
                    <td>{{ $product->deleted_at->diffForHumans() }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            {{-- RESTORE BUTTON --}}
                            <form action="{{ route('admin.products.restore', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>
                            </form>

                            {{-- PERMANENT DELETE BUTTON --}}
                            <form action="{{ route('admin.products.forceDelete', $product->id) }}" method="POST" onsubmit="return confirm('WARNING: This will permanently erase the product. Continue?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-x-circle"></i> Delete Forever
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4">Trash is empty.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            {{ $trashedProducts->links() }}
        </div>
    </div>
</div>
@endsection
