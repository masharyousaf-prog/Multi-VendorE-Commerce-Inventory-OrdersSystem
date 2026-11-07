@extends('layouts.app')

@push('nav_items')
    
@endpush

@section('content')
    {{-- This effectively passes the content through to the parent --}}
    @yield('admin_content')
@endsection
