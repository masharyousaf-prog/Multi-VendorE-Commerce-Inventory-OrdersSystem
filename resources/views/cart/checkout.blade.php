@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>Checkout</h2>
        <hr>
        <div class="card">
            <div class="card-header">Shipping Details</div>
            <div class="card-body">
                <form action="{{ route('checkout.place') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name ?? '' }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Shipping Address</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>

                    <div class="alert alert-info">
                        <strong>Total to Pay:</strong>
                        {{-- Calculate total again or pass it from controller --}}
                        ${{ number_format($cart->items->sum(fn($i) => $i->quantity * $i->product->price), 2) }}
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">Place Order</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
