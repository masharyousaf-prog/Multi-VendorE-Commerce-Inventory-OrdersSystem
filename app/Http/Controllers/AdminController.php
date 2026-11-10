<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Core KPIs
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_price');
        $platformCommission = $totalRevenue * 0.10; // 10% platform take-rate
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalVendors = User::where('role', 'vendor')->count();

        // Graph 1: 14-Day Revenue & Order Velocity (Dual-Axis Area + Column Chart)
        $dates = collect(range(13, 0))->map(fn ($days) => Carbon::today()->subDays($days)->format('Y-m-d'));
        $dailyStats = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->where('created_at', '>=', Carbon::today()->subDays(13))
            ->where('status', '!=', 'cancelled')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $revenueTrendLabels = $dates->map(fn ($d) => Carbon::parse($d)->format('M d'))->values();
        $revenueTrendData = $dates->map(fn ($d) => (float) ($dailyStats[$d]->revenue ?? 0))->values();
        $orderTrendData = $dates->map(fn ($d) => (int) ($dailyStats[$d]->orders_count ?? 0))->values();

        // Graph 2: Revenue by Category (Doughnut Chart)
        $categorySales = Category::select('categories.name', DB::raw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as revenue'))
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Graph 3: Top 5 Performing Vendors by GMV (Horizontal Bar Chart)
        $topVendors = User::where('role', 'vendor')
            ->select('users.id', 'users.name', 'users.store_name', DB::raw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as total_gmv'))
            ->leftJoin('products', 'users.id', '=', 'products.vendor_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('users.id', 'users.name', 'users.store_name')
            ->orderByDesc('total_gmv')
            ->take(5)
            ->get();

        // Graph 4: Order Fulfillment Pipeline Breakdown (Polar Area / Radial Chart)
        $orderStatusBreakdown = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Graph 5: User Acquisition Growth (Last 6 Months Stacked Bar: Customers vs Vendors)
        $months = collect(range(5, 0))->map(fn ($m) => Carbon::now()->subMonths($m)->format('Y-m'));
        $monthLabels = $months->map(fn ($m) => Carbon::parse($m . '-01')->format('M Y'))->values();
        $customerGrowth = $months->map(fn ($m) => User::where('role', 'customer')->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$m])->count())->values();
        $vendorGrowth = $months->map(fn ($m) => User::where('role', 'vendor')->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$m])->count())->values();

        $recentOrders = Order::with('user')->latest()->take(7)->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'platformCommission',
            'totalOrders',
            'totalProducts',
            'totalCustomers',
            'totalVendors',
            'revenueTrendLabels',
            'revenueTrendData',
            'orderTrendData',
            'categorySales',
            'topVendors',
            'orderStatusBreakdown',
            'monthLabels',
            'customerGrowth',
            'vendorGrowth',
            'recentOrders'
        ));
    }

    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->role === 'admin') {
            return back()->withErrors(['error' => 'Cannot block an administrator account.']);
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', "User {$user->name} status updated to " . ($user->is_active ? 'Approved' : 'Blocked') . '.');
    }

    public function products()
    {
        $products = Product::with(['vendor', 'category'])->latest()->paginate(20);
        return view('admin.products', compact('products'));
    }

    public function deleteProduct(Product $product)
    {
        // Requirement 2: Removed ProductDeletedNotification dispatch
        $product->delete();
        return back()->with('success', 'Product moved to trash.');
    }

    public function trashedProducts()
    {
        $products = Product::onlyTrashed()->with('vendor')->latest()->paginate(20);
        return view('admin.products.trashed', compact('products'));
    }

    public function restoreProduct($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return back()->with('success', 'Product restored successfully.');
    }

    public function forceDeleteProduct($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();
        return back()->with('success', 'Product permanently deleted.');
    }

    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:1',
            'min_order_amount' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        Coupon::create($validated);

        return back()->with('success', 'Promotional coupon created.');
    }
}
