@extends('layouts.app')

@section('content')
<h2>Your Shopping Cart</h2>

@if($cartItems->isEmpty())
    <div class="alert alert-warning mt-4">Your cart is empty. <a href="{{ url('/') }}">Go Shopping</a></div>
@else
    <table class="table table-bordered mt-4 bg-white align-middle">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cartItems as $item)
            <tr>
                <td>
                    <strong>{{ $item->product->name }}</strong>
                </td>

                {{-- 1. UPDATED PRICE COLUMN --}}
                <td>
                    @if($item->product->discount > 0)
                        {{-- Show Discounted Price --}}
                        <span class="text-danger fw-bold">${{ number_format($item->product->final_price, 2) }}</span>
                        <div class="small">
                            <span class="text-muted text-decoration-line-through">${{ number_format($item->product->price, 2) }}</span>
                            <span class="badge bg-danger"> -{{ $item->product->discount }}% </span>
                        </div>
                    @else
                        {{-- Show Regular Price --}}
                        ${{ number_format($item->product->price, 2) }}
                    @endif
                </td>

                <td>
                    {{ $item->quantity }}
                </td>

                {{-- 2. UPDATED ROW TOTAL COLUMN (Uses final_price) --}}
                <td>
                    ${{ number_format($item->product->final_price * $item->quantity, 2) }}
                </td>

                <td>
                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                {{-- Ensure your Controller calculates $total using final_price too! --}}
                <td colspan="2"><strong>${{ number_format($total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="d-flex justify-content-end">
        <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg">Proceed to Checkout</a>
    </div>
@endif
@endsection
