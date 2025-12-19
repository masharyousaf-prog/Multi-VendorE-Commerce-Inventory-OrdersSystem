@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>Checkout</h2>
        <hr>

        {{-- NEW SECTION: Order Summary (Lists items properly using a loop) --}}
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Order Summary</div>
            <ul class="list-group list-group-flush">
                @foreach($cart->items as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $item->product->name }}</strong>
                            <div class="small text-muted">
                                {{-- Shows Qty x Price per item --}}
                                {{ $item->quantity }} x ${{ number_format($item->product->final_price, 2) }}
                            </div>
                        </div>
                        {{-- Row Total --}}
                        <span>${{ number_format($item->product->final_price * $item->quantity, 2) }}</span>
                    </li>
                @endforeach

                {{-- Grand Total Row --}}
                <li class="list-group-item d-flex justify-content-between fw-bold bg-white">
                    <span>Grand Total</span>
                    <span>${{ number_format($total, 2) }}</span>
                </li>
            </ul>
        </div>

        {{-- Shipping Form --}}
        <div class="card">
            <div class="card-header bg-primary text-white">Shipping Details</div>
            <div class="card-body">
                <form action="{{ route('checkout.place') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        {{-- Readonly because it pulls from their account --}}
                        <input type="text" class="form-control" value="{{ Auth::user()->name ?? '' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shipping Address</label>
                        <textarea name="address" class="form-control" rows="3" required placeholder="Enter full delivery address..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" required placeholder="+1 234 567 8900">
                    </div>

                    {{-- FIXED SECTION: Uses $total instead of $item --}}
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <strong>Total to Pay:</strong>
                        <span class="fs-4 fw-bold">${{ number_format($total, 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">Confirm & Place Order</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
