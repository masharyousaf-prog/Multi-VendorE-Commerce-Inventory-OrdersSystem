<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')->get();

        $query = Product::with(['vendor:id,name,store_name,store_slug', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn ($vq) => $vq->where('store_name', 'like', "%{$search}%"));
            });
        }

        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', fn ($cq) => $cq->where('slug', $categorySlug));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }
        if ($request->boolean('flash_deals')) {
            $query->where('is_flash_deal', true);
        }

        match ($request->input('sort', 'featured')) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            'popular' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        // Issue 2 Fix: Strictly fetch products enrolled in Lightning Deals, newest first
        $flashDeals = Product::with(['vendor:id,name,store_name,store_slug', 'category'])
            ->withAvg('reviews', 'rating')
            ->where('is_flash_deal', true)
            ->where('stock', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        $topRatedProducts = Product::with(['vendor:id,name,store_name,store_slug', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->take(4)
            ->get();

        $wishlistProductIds = (Auth::check() && Auth::user()->role === 'customer')
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray()
            : [];

        return view('products.index', compact(
            'products',
            'categories',
            'flashDeals',
            'topRatedProducts',
            'wishlistProductIds'
        ));
    }

    public function show(Product $product)
    {
        $product->increment('views_count');
        $product->load(['vendor', 'category', 'reviews.user']);

        $relatedProducts = Product::with('vendor')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        $inWishlist = (Auth::check() && Auth::user()->role === 'customer')
            ? Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists()
            : false;

        return view('products.show', compact('product', 'relatedProducts', 'inWishlist'));
    }

    public function wishlistIndex()
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Only customer accounts have a wishlist.');
        }

        $wishlistItems = Wishlist::with(['product.vendor', 'product.category'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('products.wishlist', compact('wishlistItems'));
    }

    public function storeReview(Request $request, Product $product)
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Only customer accounts can submit product reviews.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:120',
            'comment' => 'required|string|max:1000',
        ]);

        $verified = Order::where('user_id', Auth::id())
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        Review::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $product->id],
            [
                'rating' => $validated['rating'],
                'title' => $validated['title'],
                'comment' => $validated['comment'],
                'is_verified_purchase' => $verified,
            ]
        );

        return back()->with('success', 'Thank you! Your review has been published.');
    }

    public function toggleWishlist(Request $request, Product $product)
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Only customers can use the wishlist.'], 403);
            }
            abort(403, 'Only customer accounts can save items to a wishlist.');
        }

        $existing = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
            $inWishlist = false;
        } else {
            Wishlist::create(['user_id' => Auth::id(), 'product_id' => $product->id]);
            $status = 'added';
            $inWishlist = true;
        }

        $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => $status,
                'in_wishlist' => $inWishlist,
                'wishlist_count' => $wishlistCount,
                'message' => $inWishlist ? "\"{$product->name}\" saved to your wishlist!" : "\"{$product->name}\" removed from wishlist.",
            ]);
        }

        return back()->with('success', $inWishlist ? 'Added to your Wishlist!' : 'Removed from Wishlist.');
    }

    public function vendorStore(string $slug)
    {
        $vendor = User::where('role', 'vendor')
            ->where(fn ($q) => $q->where('store_slug', $slug)->orWhere('id', $slug))
            ->firstOrFail();

        $products = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->where(function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)->orWhere('user_id', $vendor->id);
            })
            ->latest()
            ->paginate(12);

        $wishlistProductIds = (Auth::check() && Auth::user()->role === 'customer')
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray()
            : [];

        return view('products.vendor_store', compact('vendor', 'products', 'wishlistProductIds'));
    }
}
