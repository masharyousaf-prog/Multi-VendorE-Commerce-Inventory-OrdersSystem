<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    public function dashboard()
    {
        $vendor = Auth::user();
        $products = Product::with('category')
            ->where(function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                  ->orWhere('user_id', $vendor->id);
            })
            ->latest()
            ->get();

        $productIds = $products->pluck('id');
        $commissionRate = (float) ($vendor->commission_rate ?? 10);
        $netMultiplier = 1 - ($commissionRate / 100);

        $orderItems = OrderItem::with(['order.user', 'product'])
            ->whereIn('product_id', $productIds)
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->latest()
            ->get();

        $grossEarnings = $orderItems->sum(fn ($item) => $item->price * $item->quantity);
        $netEarnings = $grossEarnings * $netMultiplier;
        $totalUnitsSold = $orderItems->sum('quantity');
        $lowStockCount = $products->where('stock', '<=', 5)->count();

        // Count of new/unfulfilled order items requiring vendor action
        $pendingOrdersCount = OrderItem::whereIn('product_id', $productIds)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        // Chart 1: 14-Day Store Earnings & Units Sold (Dual-Line, Dual Axis)
        $dates = collect(range(13, 0))->map(fn ($d) => Carbon::today()->subDays($d)->format('Y-m-d'));
        $dailyVendorSales = OrderItem::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(price * quantity) as revenue'),
                DB::raw('SUM(quantity) as units')
            )
            ->whereIn('product_id', $productIds)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::today()->subDays(13))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = $dates->map(fn ($d) => Carbon::parse($d)->format('M d'))->values();
        $chartRevenue = $dates->map(fn ($d) => round((float) ($dailyVendorSales[$d]->revenue ?? 0), 2))->values();
        $chartUnits = $dates->map(fn ($d) => (int) ($dailyVendorSales[$d]->units ?? 0))->values();

        // Chart 2: 6-Month Monthly Gross Revenue vs Net Payout Trend
        $months = collect(range(5, 0))->map(fn ($m) => Carbon::now()->subMonths($m)->format('Y-m'));
        $monthLabels = $months->map(fn ($m) => Carbon::parse($m . '-01')->format('M Y'))->values();
        $monthlySales = OrderItem::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('SUM(price * quantity) as gross')
            )
            ->whereIn('product_id', $productIds)
            ->where('status', '!=', 'cancelled')
            ->groupBy('ym')
            ->pluck('gross', 'ym');

        $monthlyGrossData = $months->map(fn ($m) => round((float) ($monthlySales[$m] ?? 0), 2))->values();
        $monthlyNetData = $months->map(fn ($m) => round(((float) ($monthlySales[$m] ?? 0)) * $netMultiplier, 2))->values();

        // Chart 3: Top 6 SKUs by Revenue
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(price * quantity) as revenue'), DB::raw('SUM(quantity) as units_sold'))
            ->with('product:id,name')
            ->whereIn('product_id', $productIds)
            ->where('status', '!=', 'cancelled')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->take(6)
            ->get();

        // Chart 4: Vendor Revenue by Product Category
        $vendorCategorySales = Category::select('categories.name', DB::raw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as revenue'))
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->whereIn('products.id', $productIds)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Chart 5: Order Fulfillment Status Breakdown for Vendor's Items
        $vendorOrderStatus = OrderItem::whereIn('product_id', $productIds)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Chart 6: Inventory Health Matrix
        $stockHealth = [
            'Healthy (>15)' => $products->where('stock', '>', 15)->count(),
            'Moderate (6-15)' => $products->whereBetween('stock', [6, 15])->count(),
            'Low Stock (1-5)' => $products->whereBetween('stock', [1, 5])->count(),
            'Out of Stock (0)' => $products->where('stock', '<=', 0)->count(),
        ];

        return view('vendor.dashboard', compact(
            'vendor',
            'products',
            'grossEarnings',
            'netEarnings',
            'totalUnitsSold',
            'lowStockCount',
            'pendingOrdersCount',
            'chartLabels',
            'chartRevenue',
            'chartUnits',
            'monthLabels',
            'monthlyGrossData',
            'monthlyNetData',
            'topProducts',
            'vendorCategorySales',
            'vendorOrderStatus',
            'stockHealth'
        ));
    }

    /**
     * Vendor Order Management & New Order Alerts Console
     */
    public function orders(Request $request)
    {
        $vendor = Auth::user();
        $productIds = Product::where('vendor_id', $vendor->id)
            ->orWhere('user_id', $vendor->id)
            ->pluck('id');

        $query = OrderItem::with(['order.user', 'product'])
            ->whereIn('product_id', $productIds);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orderItems = $query->latest()->paginate(15)->withQueryString();

        $statusCounts = OrderItem::whereIn('product_id', $productIds)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('vendor.orders', compact('vendor', 'orderItems', 'statusCounts'));
    }

    /**
     * Update Vendor Order Item Status & Synchronize Parent Order Tracking Status
     */
    public function updateOrderStatus(Request $request, OrderItem $orderItem)
    {
        $vendor = Auth::user();
        $product = $orderItem->product;

        if (!$product || ($product->vendor_id !== $vendor->id && $product->user_id !== $vendor->id)) {
            abort(403, 'Unauthorized to manage this order item.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,packed,shipped,delivered,cancelled',
        ]);

        $previousStatus = $orderItem->status;
        $newStatus = $validated['status'];

        DB::transaction(function () use ($orderItem, $previousStatus, $newStatus, $product) {
            if ($newStatus === 'cancelled' && $previousStatus !== 'cancelled') {
                $product->increment('stock', $orderItem->quantity);
            } elseif ($previousStatus === 'cancelled' && $newStatus !== 'cancelled') {
                $product->decrement('stock', min($product->stock, $orderItem->quantity));
            }

            $orderItem->update(['status' => $newStatus]);

            // Synchronize parent Order status based on its items' statuses
            $order = $orderItem->order()->with('items')->first();
            if ($order) {
                $itemStatuses = $order->items->pluck('status')->unique()->values();
                if ($itemStatuses->count() === 1) {
                    $order->update(['status' => $itemStatuses->first()]);
                } else {
                    $rankOrder = ['pending' => 1, 'processing' => 2, 'packed' => 3, 'shipped' => 4, 'delivered' => 5];
                    $activeRanks = $order->items
                        ->where('status', '!=', 'cancelled')
                        ->map(fn ($i) => $rankOrder[$i->status] ?? 1);

                    if ($activeRanks->isNotEmpty()) {
                        $minRank = $activeRanks->min();
                        $syncedStatus = array_search($minRank, $rankOrder, true) ?: 'processing';
                        $order->update(['status' => $syncedStatus]);
                    }
                }
            }
        });

        return back()->with('success', "Order #{$orderItem->order_id} item status updated to " . strtoupper($newStatus) . ".");
    }

    /**
     * Update Vendor Storefront Settings
     */
    public function updateStoreSettings(Request $request)
    {
        $vendor = Auth::user();
        $validated = $request->validate([
            'store_name' => 'required|string|max:120',
            'store_description' => 'nullable|string|max:1000',
        ]);

        $vendor->update([
            'store_name' => $validated['store_name'],
            'store_slug' => Str::slug($validated['store_name'] . '-' . $vendor->id),
            'store_description' => $validated['store_description'] ?? null,
        ]);

        return back()->with('success', 'Storefront profile updated successfully.');
    }

    public function products(Request $request)
    {
        $vendor = Auth::user();
        $query = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->where(function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                  ->orWhere('user_id', $vendor->id);
            });

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        return view('vendor.products.index', compact('vendor', 'products'));
    }

    public function createProduct()
    {
        $categories = Category::all();
        return view('vendor.products.create', compact('categories'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:60|unique:products,sku',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|gt:price',
            'stock' => 'required|integer|min:0',
            'is_flash_deal' => 'nullable|boolean',
            'image_url' => 'nullable|url|max:1000',
            'image_base64' => 'nullable|string',
        ]);

        $imagePath = $this->resolveProductImage($request);

        Product::create([
            'vendor_id' => Auth::id(),
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'brand' => !empty($validated['brand']) ? $validated['brand'] : 'Generic',
            'sku' => !empty($validated['sku']) ? strtoupper($validated['sku']) : strtoupper('SKU-' . Str::random(8)),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'stock' => $validated['stock'],
            'is_flash_deal' => $request->boolean('is_flash_deal'),
            'image' => $imagePath,
        ]);

        return redirect()->route('vendor.products.index')->with('success', 'Product published to marketplace.');
    }

    public function editProduct(Product $product)
    {
        if ($product->vendor_id !== Auth::id() && $product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this product.');
        }
        $categories = Category::all();
        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        if ($product->vendor_id !== Auth::id() && $product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this product.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:60|unique:products,sku,' . $product->id,
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|gt:price',
            'stock' => 'required|integer|min:0',
            'is_flash_deal' => 'nullable|boolean',
            'image_url' => 'nullable|url|max:1000',
            'image_base64' => 'nullable|string',
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'brand' => !empty($validated['brand']) ? $validated['brand'] : 'Generic',
            'sku' => !empty($validated['sku']) ? strtoupper($validated['sku']) : $product->sku,
            'description' => $validated['description'],
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'stock' => $validated['stock'],
            'is_flash_deal' => $request->boolean('is_flash_deal'),
        ];

        if ($newImage = $this->resolveProductImage($request)) {
            $data['image'] = $newImage;
        }

        $product->update($data);

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function deleteProduct(Product $product)
    {
        if ($product->vendor_id !== Auth::id() && $product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this product.');
        }

        $product->delete();
        return redirect()->route('vendor.products.index')->with('success', 'Product removed from your listings.');
    }

    private function resolveProductImage(Request $request): ?string
    {
        Storage::disk('public')->makeDirectory('products');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            return $request->file('image')->store('products', 'public');
        }

        if ($request->filled('image_base64') && str_starts_with($request->input('image_base64'), 'data:image/')) {
            $parts = explode(',', $request->input('image_base64'), 2);
            if (count($parts) === 2) {
                $binary = base64_decode($parts[1]);
                if ($binary !== false) {
                    $filename = 'products/' . Str::random(32) . '.jpg';
                    Storage::disk('public')->put($filename, $binary);
                    return $filename;
                }
            }
        }

        if ($request->filled('image_url')) {
            return trim($request->input('image_url'));
        }

        return null;
    }
}
