<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::with(['items.product.vendor'])->firstOrCreate(['user_id' => Auth::id()]);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        if ($product->stock < 1) {
            return back()->withErrors(['error' => 'Product is out of stock.']);
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $qtyToAdd = max(1, (int) $request->input('quantity', 1));

        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->first();
        if ($item) {
            $item->update(['quantity' => min($product->stock, $item->quantity + $qtyToAdd)]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => min($product->stock, $qtyToAdd),
            ]);
        }

        return redirect()->route('cart.index')->with('success', "{$product->name} added to your cart.");
    }

    public function update(Request $request, CartItem $item)
    {
        $qty = max(1, (int) $request->input('quantity', 1));
        $item->update(['quantity' => min($item->product->stock, $qty)]);
        return back()->with('success', 'Cart quantity updated.');
    }

    public function remove(CartItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item removed from cart.');
    }

    public function checkout(Request $request)
    {
        $cart = Cart::with('items.product')->where('user_id', Auth::id())->first();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('products.index')->withErrors(['error' => 'Your cart is empty.']);
        }

        // If GET or preview step, render checkout page
        if ($request->isMethod('get') || !$request->has('confirm_order')) {
            return view('cart.checkout', compact('cart'));
        }

        return DB::transaction(function () use ($request, $cart) {
            $subtotal = $cart->items->sum(fn ($i) => $i->product->price * $i->quantity);
            $discount = 0;
            $couponCode = strtoupper(trim((string) $request->input('coupon_code')));

            if ($couponCode === 'MEGA20') {
                $discount = round($subtotal * 0.20, 2);
            } elseif ($couponCode && ($coupon = Coupon::where('code', $couponCode)->first())) {
                if ($coupon->isValidFor($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                }
            }

            $finalTotal = max(0, $subtotal - $discount);
            $trackingNumber = 'NXM-' . strtoupper(Str::random(4)) . '-' . rand(100000, 999999);

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $finalTotal,
                'status' => 'completed',
                'tracking_number' => $trackingNumber,
                'coupon_code' => $discount > 0 ? $couponCode : null,
                'discount_amount' => $discount,
                'payment_method' => $request->input('payment_method', 'card'),
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
                $item->product->decrement('stock', min($item->product->stock, $item->quantity));
            }

            $cart->items()->delete();

            return view('cart.checkout', [
                'cart' => $cart,
                'completedOrder' => $order->load('items.product'),
            ]);
        });
    }
}
