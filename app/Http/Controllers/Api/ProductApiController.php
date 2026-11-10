<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductApiController extends Controller
{
    /**
     * GET /api/products
     * Supports ?q=, ?category=, ?min_price=, ?max_price=, ?flash_deals=1, ?sort=
     */
    public function index(Request $request)
    {
        $query = Product::with(['vendor:id,name,store_name,store_slug', 'category:id,name,slug'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->whereHas('category', fn ($cq) => $cq->where('slug', $category));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('flash_deals')) {
            $query->where('is_flash_deal', true);
        }

        $products = $query->latest()->paginate(12);

        return response()->json([
            'status' => 'success',
            'total_products' => $products->total(),
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
            ],
        ]);
    }

    /**
     * GET /api/products/flash-deals
     */
    public function flashDeals()
    {
        $deals = Product::with(['vendor:id,name,store_name', 'category:id,name,slug'])
            ->where('is_flash_deal', true)
            ->where('stock', '>', 0)
            ->take(8)
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $deals->count(),
            'data' => $deals,
        ]);
    }

    /**
     * GET /api/categories
     */
    public function categories()
    {
        return response()->json([
            'status' => 'success',
            'data' => Category::withCount('products')->get(),
        ]);
    }

    /**
     * GET /api/products/{product}
     */
    public function show(Product $product)
    {
        $product->load(['vendor:id,name,store_name,store_slug', 'category', 'reviews.user:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => $product,
                'average_rating' => $product->average_rating,
                'discount_percentage' => $product->discount_percentage,
            ],
        ]);
    }

    /**
     * POST /api/products (JWT Protected: Vendor / Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|gt:price',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string',
            'is_flash_deal' => 'nullable|boolean',
        ]);

        $product = Product::create(array_merge($validated, [
            'vendor_id' => Auth::id(),
            'sku' => 'NXM-' . strtoupper(Str::random(6)),
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Product created via JWT API',
            'data' => $product,
        ], 201);
    }

    /**
     * PUT /api/products/{product} (JWT Protected: Vendor / Admin)
     */
    public function update(Request $request, Product $product)
    {
        if (Auth::user()->role !== 'admin' && $product->vendor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized to modify this product'], 403);
        }

        $product->update($request->only([
            'name', 'category_id', 'brand', 'price', 'compare_at_price', 'stock', 'description', 'is_flash_deal'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * DELETE /api/products/{product} (JWT Protected: Vendor / Admin)
     */
    public function destroy(Product $product)
    {
        if (Auth::user()->role !== 'admin' && $product->vendor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized to delete this product'], 403);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product soft-deleted',
        ]);
    }
}
