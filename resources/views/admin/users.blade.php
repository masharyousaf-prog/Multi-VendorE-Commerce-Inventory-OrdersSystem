@extends('layouts.admin')

@section('admin_content')
<h2>Manage Users</h2>
<table class="table table-striped mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'vendor' ? 'info' : 'secondary') }}">
                    {{ ucfirst($user->role) }}
                </span>
            </td>
            <td>{{ $user->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Pagination Links --}}
<div class="d-flex justify-content-center">
    {{ $users->links() }}
</div>
@endsection
