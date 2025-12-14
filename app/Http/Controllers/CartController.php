<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
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

        // If no cart exists yet, pass an empty collection or null
        $cartItems = $cart ? $cart->items()->with('product')->get() : collect();

        // Calculate total
        $total = $cartItems->sum(function($item) {
            return $item->quantity * $item->product->price;
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

        return view('cart.checkout', compact('cart'));
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

        // 2. REDUCE STOCK (The Missing Logic)
        // We loop through each item in the cart
        foreach ($cart->items as $item) {
            $product = $item->product;

            // Check if we actually have enough stock before subtracting
            if ($product->stock >= $item->quantity) {
                // specific Laravel helper that subtracts X from the column
                $product->decrement('stock', $item->quantity);
            } else {
                // Optional: Stop the order if someone bought the item while you were browsing
                return redirect()->back()->with('error', 'Sorry, ' . $product->name . ' is now out of stock.');
            }
        }

        // 3. Clear the Cart
        // Now that stock is updated, we can remove items from the cart
        $cart->items()->delete();

        return redirect('/')->with('success', 'Order placed successfully! Thank you for shopping.');
    }
}
