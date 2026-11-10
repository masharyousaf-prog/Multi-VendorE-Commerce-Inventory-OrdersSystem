<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    public function dashboard()
    {
        $vendor = Auth::user();
        $products = Product::with('category')->where('vendor_id', $vendor->id)->latest()->get();
        $productIds = $products->pluck('id');

        $orderItems = OrderItem::with(['order.user', 'product'])
            ->whereIn('product_id', $productIds)
            ->latest()
            ->get();

        $grossEarnings = $orderItems->sum(fn ($item) => $item->price * $item->quantity);
        $netEarnings = $grossEarnings * (1 - (($vendor->commission_rate ?? 10) / 100));
        $totalUnitsSold = $orderItems->sum('quantity');
        $lowStockCount = $products->where('stock', '<=', 5)->count();

        // Chart 1: 14-Day Vendor Sales & Units Sold
        $dates = collect(range(13, 0))->map(fn ($d) => Carbon::today()->subDays($d)->format('Y-m-d'));
        $dailyVendorSales = OrderItem::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(price * quantity) as revenue'),
                DB::raw('SUM(quantity) as units')
            )
            ->whereIn('product_id', $productIds)
            ->where('created_at', '>=', Carbon::today()->subDays(13))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = $dates->map(fn ($d) => Carbon::parse($d)->format('M d'))->values();
        $chartRevenue = $dates->map(fn ($d) => (float) ($dailyVendorSales[$d]->revenue ?? 0))->values();
        $chartUnits = $dates->map(fn ($d) => (int) ($dailyVendorSales[$d]->units ?? 0))->values();

        // Chart 2: Top Products by Revenue (Bar/Donut Chart)
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(price * quantity) as revenue'), DB::raw('SUM(quantity) as units_sold'))
            ->with('product:id,name')
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->take(6)
            ->get();

        // Chart 3: Inventory Health Distribution (Healthy >15, Moderate 6-15, Critical 1-5, Out of Stock 0)
        $stockHealth = [
            'Healthy (>15)' => $products->where('stock', '>', 15)->count(),
            'Moderate (6-15)' => $products->whereBetween('stock', [6, 15])->count(),
            'Low Stock (1-5)' => $products->whereBetween('stock', [1, 5])->count(),
            'Out of Stock (0)' => $products->where('stock', '<=', 0)->count(),
        ];

        $categories = Category::all();

        return view('vendor.dashboard', compact(
            'vendor',
            'products',
            'orderItems',
            'grossEarnings',
            'netEarnings',
            'totalUnitsSold',
            'lowStockCount',
            'chartLabels',
            'chartRevenue',
            'chartUnits',
            'topProducts',
            'stockHealth',
            'categories'
        ));
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
            'flash_deal_ends_at' => 'nullable|date',
            'image' => 'nullable|image|max:4096',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        Product::create([
            'vendor_id' => Auth::id(),
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? 'Generic',
            'sku' => $validated['sku'] ?? strtoupper('SKU-' . Str::random(8)),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'stock' => $validated['stock'],
            'is_flash_deal' => $request->boolean('is_flash_deal'),
            'flash_deal_ends_at' => $validated['flash_deal_ends_at'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()->route('vendor.dashboard')->with('success', 'Product published to marketplace.');
    }
}
