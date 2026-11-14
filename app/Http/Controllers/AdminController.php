<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_price');
        $platformCommission = $totalRevenue * 0.10;
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalVendors = User::where('role', 'vendor')->count();

        // 14-Day Revenue & Order Velocity
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

        // Revenue by Category
        $categorySales = Category::select('categories.name', DB::raw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as revenue'))
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Top 5 Vendors by GMV
        $topVendors = User::where('role', 'vendor')
            ->select('users.id', 'users.name', 'users.store_name', DB::raw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as total_gmv'))
            ->leftJoin('products', 'users.id', '=', 'products.vendor_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('users.id', 'users.name', 'users.store_name')
            ->orderByDesc('total_gmv')
            ->take(6)
            ->get();

        // Order Status Breakdown
        $orderStatusBreakdown = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // 6-Month User Growth
        $months = collect(range(5, 0))->map(fn ($m) => Carbon::now()->subMonths($m)->format('Y-m'));
        $monthLabels = $months->map(fn ($m) => Carbon::parse($m . '-01')->format('M Y'))->values();
        $customerGrowth = $months->map(fn ($m) => User::where('role', 'customer')->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$m])->count())->values();
        $vendorGrowth = $months->map(fn ($m) => User::where('role', 'vendor')->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$m])->count())->values();

        $recentOrders = Order::with('user')->latest()->take(8)->get();

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

    public function orders(Request $request)
    {
        $query = Order::with(['user', 'items.product.vendor']);
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('tracking_number', 'like', "%{$q}%")
                    ->orWhere('id', ltrim($q, '#'));
            });
        }
        $orders = $query->latest()->paginate(15)->withQueryString();
        return view('admin.orders', compact('orders'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,packed,shipped,delivered,cancelled',
        ]);

        $previousStatus = $order->status;
        $newStatus = $validated['status'];

        DB::transaction(function () use ($order, $previousStatus, $newStatus) {
            if ($newStatus === 'cancelled' && $previousStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::withTrashed()->where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }
            }
            $order->update(['status' => $newStatus]);
            $order->items()->update(['status' => $newStatus]);
        });

        return back()->with('success', "Order #{$order->id} status updated to " . strtoupper($newStatus) . ".");
    }

    public function coupons()
    {
        $coupons = Coupon::latest()->get();
        return view('admin.coupons', compact('coupons'));
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

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = true;
        Coupon::create($validated);

        return back()->with('success', 'Promotional coupon created.');
    }

    public function toggleCoupon(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', "Coupon {$coupon->code} status updated.");
    }

    public function deleteCoupon(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon deleted.');
    }

    public function customerReport()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders', 'total_price')
            ->with(['orders.items'])
            ->latest()
            ->get();

        return view('admin.reports.customer_report', compact('customers'));
    }

    public function vendorReport()
    {
        $vendors = User::where('role', 'vendor')
            ->withCount('products')
            ->with(['products.orderItems'])
            ->get()
            ->map(function ($vendor) {
                $gmv = $vendor->products->sum(function ($product) {
                    return $product->orderItems->where('status', '!=', 'cancelled')->sum(fn ($item) => $item->price * $item->quantity);
                });
                $vendor->total_revenue = $gmv;
                $vendor->total_units_sold = $vendor->products->sum(fn ($p) => $p->orderItems->where('status', '!=', 'cancelled')->sum('quantity'));
                return $vendor;
            });

        return view('admin.reports.vendor_report', compact('vendors'));
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
        return back()->with('success', "User {$user->name} status updated to " . ($user->is_active ? 'Approved' : 'Suspended') . '.');
    }

    public function products()
    {
        $products = Product::with(['vendor', 'category'])->latest()->paginate(20);
        return view('admin.products', compact('products'));
    }

    public function deleteProduct(Product $product)
    {
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
}
