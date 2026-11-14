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
    private function ensureCustomerRole(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Only customer accounts can use the shopping cart.');
        }
    }

    public function index()
    {
        $this->ensureCustomerRole();
        $cart = Cart::with(['items.product.vendor'])->firstOrCreate(['user_id' => Auth::id()]);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $this->ensureCustomerRole();

        if ($product->stock < 1) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Product is out of stock.'], 422);
            }
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

        $totalCartCount = (int) CartItem::where('cart_id', $cart->id)->sum('quantity');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "\"{$product->name}\" added to your cart!",
                'cart_count' => $totalCartCount,
            ]);
        }

        return back()->with('success', "\"{$product->name}\" added to your shopping cart.");
    }

    public function update(Request $request, CartItem $item)
    {
        $this->ensureCustomerRole();
        if ($item->cart->user_id !== Auth::id()) {
            abort(403);
        }
        $qty = max(1, (int) $request->input('quantity', 1));
        $item->update(['quantity' => min($item->product->stock, $qty)]);
        return back()->with('success', 'Cart quantity updated.');
    }

    public function remove(CartItem $item)
    {
        $this->ensureCustomerRole();
        if ($item->cart->user_id !== Auth::id()) {
            abort(403);
        }
        $item->delete();
        return back()->with('success', 'Item removed from cart.');
    }

    public function checkout(Request $request)
    {
        $this->ensureCustomerRole();
        $cart = Cart::with('items.product.vendor')->where('user_id', Auth::id())->first();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('products.index')->withErrors(['error' => 'Your cart is empty.']);
        }

        if ($request->isMethod('get') || !$request->has('confirm_order')) {
            return view('cart.checkout', compact('cart'));
        }

        $shipping = $request->validate([
            'shipping_name' => 'required|string|max:120',
            'shipping_phone' => 'required|string|max:40',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:30',
            'coupon_code' => 'nullable|string|max:40',
        ]);

        return DB::transaction(function () use ($request, $cart, $shipping) {
            $subtotal = 0;

            // Lock products for update to prevent race-condition overselling
            foreach ($cart->items as $item) {
                $lockedProduct = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if (!$lockedProduct || $lockedProduct->stock < $item->quantity) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => "Insufficient stock for \"{$item->product->name}\". Available: " . ($lockedProduct->stock ?? 0),
                    ]);
                }
                $subtotal += ($lockedProduct->price * $item->quantity);
            }

            $discount = 0;
            $couponCode = strtoupper(trim((string) ($shipping['coupon_code'] ?? '')));

            if ($couponCode === 'MEGA20' && $subtotal >= 50) {
                $discount = round($subtotal * 0.20, 2);
            } elseif ($couponCode && ($coupon = Coupon::where('code', $couponCode)->first())) {
                if ($coupon->isValidFor($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                }
            }

            $finalTotal = max(0, $subtotal - $discount);
            $trackingNumber = 'NXM-' . strtoupper(Str::random(4)) . '-' . rand(100000, 999999);

            // Create Order with real initial status 'pending' and Cash on Delivery ('cod')
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $finalTotal,
                'total_amount' => $finalTotal,
                'status' => 'pending',
                'tracking_number' => $trackingNumber,
                'coupon_code' => $discount > 0 ? $couponCode : null,
                'discount_amount' => $discount,
                'payment_method' => 'cod',
                'shipping_name' => $shipping['shipping_name'],
                'shipping_phone' => $shipping['shipping_phone'],
                'shipping_address' => $shipping['shipping_address'],
                'shipping_city' => $shipping['shipping_city'],
                'shipping_postal_code' => $shipping['shipping_postal_code'],
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'discount_applied' => $item->product->discount_percentage,
                    'status' => 'pending',
                ]);
                Product::where('id', $item->product_id)->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return view('cart.checkout', [
                'cart' => $cart,
                'completedOrder' => $order->fresh(['items.product.vendor']),
            ]);
        });
    }

    /**
     * Customer "My Orders" History Page
     */
    public function myOrders()
    {
        $this->ensureCustomerRole();
        $orders = Order::with(['items.product.vendor'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Customer Order Cancellation (Allowed while order status is 'pending')
     */
    public function cancelOrder(Order $order)
    {
        $this->ensureCustomerRole();
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending orders that have not entered vendor packing can be cancelled.']);
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::withTrashed()->where('id', $item->product_id)->increment('stock', $item->quantity);
                }
                $item->update(['status' => 'cancelled']);
            }
            $order->update(['status' => 'cancelled']);
        });

        return back()->with('success', "Order #{$order->id} ({$order->tracking_number}) has been cancelled and stock restored.");
    }

    /**
     * Public & Customer Order Tracking Lookup by Tracking ID
     */
    public function trackOrder(Request $request)
    {
        $trackingNumber = trim((string) $request->input('tracking_number'));
        $order = null;

        if ($trackingNumber !== '') {
            $order = Order::with(['items.product.vendor', 'user'])
                ->where('tracking_number', $trackingNumber)
                ->orWhere('id', ltrim($trackingNumber, '#'))
                ->first();
        }

        return view('orders.track', compact('order', 'trackingNumber'));
    }
}
