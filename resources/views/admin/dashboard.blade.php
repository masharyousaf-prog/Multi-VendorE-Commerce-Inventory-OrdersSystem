@extends('layouts.admin')

@section('admin_content')
<div class="row">
    <div class="col-md-12">
        <h2>Admin Console</h2>
        <hr>
    </div>
</div>

<div class="row text-center">
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
@endsection
