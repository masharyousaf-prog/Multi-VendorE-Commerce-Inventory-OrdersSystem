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

        // Main Faceted Product Query
        $query = Product::with(['vendor:id,name,store_name,store_slug', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // 1. Keyword Search (Name, Brand, SKU, Description, Vendor Store)
        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn ($vq) => $vq->where('store_name', 'like', "%{$search}%"));
            });
        }

        // 2. Category Filter
        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', fn ($cq) => $cq->where('slug', $categorySlug));
        }

        // 3. Price Range Filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        // 4. Stock & Flash Deal Filters
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }
        if ($request->boolean('flash_deals')) {
            $query->where('is_flash_deal', true);
        }

        // 5. Dynamic Sorting (Best Selling, Price Low-High, Price High-Low, Top Rated, Newest)
        match ($request->input('sort', 'featured')) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            'popular' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        // Curated Storefront Sections for Homepage Discovery
        $flashDeals = Product::with(['vendor:id,name,store_name', 'category'])
            ->withAvg('reviews', 'rating')
            ->where('stock', '>', 0)
            ->where(function ($q) {
                $q->where('is_flash_deal', true)
                  ->orWhereNotNull('compare_at_price');
            })
            ->take(4)
            ->get();

        $topRatedProducts = Product::with(['vendor:id,name,store_name', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->take(4)
            ->get();

        $wishlistProductIds = Auth::check()
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

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function storeReview(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:120',
            'comment' => 'required|string|max:1000',
        ]);

        // Check if customer actually purchased this item
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

    public function toggleWishlist(Product $product)
    {
        $existing = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
        } else {
            Wishlist::create(['user_id' => Auth::id(), 'product_id' => $product->id]);
            $status = 'added';
        }

        if (request()->expectsJson()) {
            return response()->json(['status' => $status]);
        }

        return back()->with('success', $status === 'added' ? 'Added to your Wishlist!' : 'Removed from Wishlist.');
    }

    public function vendorStore(string $slug)
    {
        $vendor = User::where('role', 'vendor')
            ->where(fn ($q) => $q->where('store_slug', $slug)->orWhere('id', $slug))
            ->firstOrFail();

        $products = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->where('vendor_id', $vendor->id)
            ->paginate(12);

        return view('products.vendor_store', compact('vendor', 'products'));
    }
}
