@extends('layouts.admin')

@section('admin_content')

<div class="row">
    <div class="col-md-12">
        <h2>Admin Console</h2>
        <hr>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row text-center mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white mb-3">
            <div class="card-body">
                <h3>{{ $stats['total_users'] }}</h3>
                <p>Total Users</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white mb-3">
            <div class="card-body">
                <h3>{{ $stats['total_products'] }}</h3>
                <p>Total Products</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-dark mb-3">
            <div class="card-body">
                <h3>{{ $stats['total_vendors'] }}</h3>
                <p>Active Vendors</p>
            </div>
        </div>
    </div>
</div>

{{-- Recent Notifications --}}
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        Recent Notifications
    </div>

    <ul class="list-group list-group-flush">
        @forelse(Auth::user()->notifications as $notification)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $notification->data['message'] ?? 'Notification message' }}
                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
            </li>

            {{-- Auto mark read --}}
            @php $notification->markAsRead(); @endphp

        @empty
            <li class="list-group-item">No new notifications.</li>
        @endforelse
    </ul>
</div>

@endsection
