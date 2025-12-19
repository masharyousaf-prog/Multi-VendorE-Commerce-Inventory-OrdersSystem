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
            <th>Login Approved?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <span class="badge bg-{{ 
                    $user->role === 'admin' ? 'danger' : 
                    ($user->role === 'vendor' ? 'info' : 'secondary') 
                }}">
                    {{ ucfirst($user->role) }}
                </span>
            </td>
            <td>{{ $user->created_at->format('Y-m-d') }}</td>
            <td>
                <div class="form-check form-switch">
                    <input 
                        class="form-check-input user-status-toggle"
                        type="checkbox"
                        data-id="{{ $user->id }}"
                        {{ $user->is_active ? 'checked' : '' }}
                        {{ $user->role === 'admin' ? 'disabled' : '' }}>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $users->links() }}
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
                'Accept': 'application/json'
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
            alert('Server error');
            checkbox.checked = !checkbox.checked;
        });
    });
});
</script>

@endsection
