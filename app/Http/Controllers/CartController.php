<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * View the Cart.
     */
    public function index()
    {
        $cart = $this->getCart();

        // If no cart exists yet, pass an empty collection
        $cartItems = $cart ? $cart->items()->with('product')->get() : collect();

        // Calculate total using FINAL PRICE (which includes discount)
        $total = $cartItems->sum(function($item) {
            // CHANGE THIS LINE: Use final_price instead of price
            return $item->quantity * $item->product->final_price;
        });

        return view('cart.index', compact('cartItems', 'total'));
    }
    /**
     * Add Item to Cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = $this->getOrCreateCart();

        // Check if item exists in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $product->id)
                            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity
            ]);
        }

        return redirect()->back()->with('success', 'Added to cart!');
    }

    /**
     * Helper: Get existing cart or create a new one.
     */
    private function getOrCreateCart()
    {
        $user = Auth::user();

        if ($user) {
            // Logged in user
            return Cart::firstOrCreate(
                ['user_id' => $user->id, 'status' => 'active']
            );
        } else {
            // Guest user (using session ID)
            $sessionId = session()->getId();
            return Cart::firstOrCreate(
                ['session_token' => $sessionId, 'status' => 'active']
            );
        }
    }

    /**
     * Helper: Get current cart for viewing.
     */
    private function getCart()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->where('status', 'active')->first();
        }
        return Cart::where('session_token', session()->getId())->where('status', 'active')->first();
    }

    public function removeItem($id)
    {
        // Find the item and make sure it belongs to the current user's cart (security check)
        // For simplicity in this lab, we will just find and delete:
        CartItem::destroy($id);

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    public function checkout()
    {
        $cart = $this->getCart();
        // If cart is empty, redirect back
        if (!$cart || $cart->items->isEmpty()) {
            return redirect('/cart')->with('error', 'Your cart is empty.');
        }

        // Calculate total using final_price (Discounted Price)
        $total = $cart->items->sum(function($item) {
            // Use final_price here, NOT price
            return $item->quantity * $item->product->final_price;
        });

        return view('cart.checkout', compact('cart','total'));
    }

    public function placeOrder(Request $request)
    {
        // 1. Validate Address Data
        $request->validate([
            'address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $cart = $this->getCart();

        // Safety Check: Is cart empty?
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Cart is empty!');
        }

        // --- NEW SECTION: Create the Main Order Record ---
        // Calculate total based on final_price (including discounts)
        $totalAmount = $cart->items->sum(function($item) {
            return $item->product->final_price * $item->quantity;
        });

        // Save to 'orders' table
        $order = Order::create([
            'user_id' => Auth::id(),
            'total_amount' => $totalAmount
        ]);
        // ------------------------------------------------

        // 2. Loop Items: Reduce Stock AND Save History
        foreach ($cart->items as $item) {
            $product = $item->product;

            // Check stock
            if ($product->stock >= $item->quantity) {

                // --- NEW SECTION: Save Item to Order History ---
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $product->name,      // Snapshot of name
                    'price' => $product->final_price,      // Snapshot of price PAID
                    'quantity' => $item->quantity,
                    'discount_applied' => $product->discount ?? 0
                ]);
                // -----------------------------------------------

                // Reduce Stock
                $product->decrement('stock', $item->quantity);

            } else {
                return redirect()->back()->with('error', 'Sorry, ' . $product->name . ' is now out of stock.');
            }
        }

        // 3. Clear the Cart
        $cart->items()->delete();

        return redirect('/')->with('success', 'Order placed successfully! Thank you for shopping.');
    }
}
