@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header & Stats --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Users</h2>
        <div>
            <span class="badge bg-secondary me-2">{{ $users->total() }} Total Records</span>
            <a href="{{ route('admin.reports.customers') }}" target="_blank" class="btn btn-sm btn-dark">
                📄 Print Customer Report
            </a>
            <a href="{{ route('admin.reports.vendors') }}" target="_blank" class="btn btn-sm btn-warning ms-2">
                📄 Print Vendor Report
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex align-items-center">
                <strong class="me-3">Filter By Role:</strong>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.users') }}"
                        class="btn btn-outline-primary {{ !request('role') || request('role') == 'all' ? 'active' : '' }}">
                        All
                    </a>
                    <a href="{{ route('admin.users', ['role' => 'admin']) }}"
                        class="btn btn-outline-danger {{ request('role') == 'admin' ? 'active' : '' }}">
                        Admins
                    </a>
                    <a href="{{ route('admin.users', ['role' => 'vendor']) }}"
                        class="btn btn-outline-warning {{ request('role') == 'vendor' ? 'active' : '' }}">
                        Vendors
                    </a>
                    <a href="{{ route('admin.users', ['role' => 'customer']) }}"
                        class="btn btn-outline-info {{ request('role') == 'customer' ? 'active' : '' }}">
                        Customers
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table Card --}}
    <div class="card shadow-sm">
        <div class="card-body p-0"> {{-- Removed padding for a flush table look --}}
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th class="text-center">Login Approved?</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3">{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $user->role === 'admin' ? 'danger' : 
                                    ($user->role === 'vendor' ? 'warning text-dark' : 'info text-dark') 
                                }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input 
                                        class="form-check-input user-status-toggle"
                                        type="checkbox"
                                        role="switch"
                                        data-id="{{ $user->id }}"
                                        {{ $user->is_active ? 'checked' : '' }}
                                        {{ $user->role === 'admin' ? 'disabled' : '' }}>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <h5 class="text-muted">No users found for this filter.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $users->appends(['role' => request('role')])->links() }}
    </div>
</div>

{{-- AJAX Script --}}
<script>
document.querySelectorAll('.user-status-toggle').forEach(toggle => {
    toggle.addEventListener('change', function () {
        let userId = this.dataset.id;
        let checkbox = this;

        fetch(`/admin/users/${userId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to update user status');
                checkbox.checked = !checkbox.checked;
            }
        })
        .catch(() => {
            alert('Server error occurred');
            checkbox.checked = !checkbox.checked;
        });
    });
});
</script>
@endsection