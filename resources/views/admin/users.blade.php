@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Users</h2>
            <span class="badge bg-secondary">{{ $users->total() }} Total Records</span>
        </div>

        <div class="card mb-4">
            <div class="card-body py-2">
                <form action="{{ route('admin.users') }}" method="GET" class="d-flex align-items-center">
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
                </form>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.reports.customers') }}" target="_blank" class="btn btn-dark ms-3">
                📄 Print Customer Report
            </a>
            <a href="{{ route('admin.reports.vendors') }}" target="_blank" class="btn btn-warning ms-2">
                📄 Print Vendor Report
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'vendor' ? 'warning text-dark' : 'info text-dark') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <h5 class="text-muted">No users found for this filter.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-3">
                    {{ $users->appends(['role' => request('role')])->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
