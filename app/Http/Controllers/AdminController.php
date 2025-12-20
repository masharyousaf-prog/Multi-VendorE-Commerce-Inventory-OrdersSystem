<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ProductDeletedNotification;

class AdminController extends Controller
{
    /**
     * Display the Admin Console / Dashboard.
     */
    public function dashboard()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $stats = [
            'total_users'    => User::count(),
            'total_vendors'  => User::where('role', 'vendor')->count(),
            'total_products' => Product::count(),
            'recent_users'   => User::latest()->take(5)->get()
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Manage Users.
     */
    public function users(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        // 1. Start the query
        $query = User::query();


        $users = User::paginate(10);

        // 2. Check for 'role' parameter in the URL (e.g., ?role=vendor)
        if ($request->has('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        // 3. Get results with pagination
        $users = $query->latest()->paginate(10);; // Pagination for better performance

        return view('admin.users', compact('users'));
    }

    /**
     * Manage Products.
     */
    public function products()
    {
        if (Auth::user()->role !== 'admin') { abort(403); }

        $products = Product::with('vendor')->latest()->paginate(10);

        return view('admin.products', compact('products'));
    }

    /**
     * Force delete a product.
     */
    public function deleteProduct($id)
    {
        if (Auth::user()->role !== 'admin') { abort(403); }

        $product = Product::findOrFail($id);

        $productName = $product->name; // Save name before deletion
        $vendor = $product->vendor;

        

        $product->delete();

        if ($vendor) {
            $vendor->notify(new ProductDeletedNotification($productName, 'Soft Deleted'));
        }

        return redirect()->back()->with('success', 'Product removed successfully.');
    }



    /**
     * NEW: Toggle User Login Status (Active/Inactive)
     */
    public function toggleStatus($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $user = User::findOrFail($id);

        $user->is_active = !$user->is_active; // Switch between 1 & 0
        $user->save();

        return response()->json([
            'success' => true,
            'status' => $user->is_active ? 'Active' : 'Inactive'
        ]);}

    /**
     * Display users filtered by their role.
     */
    public function usersByRole($role)
    {
        // Security check
        if (Auth::user()->role !== 'admin') { abort(403); }

        // Filter users by the role passed in the URL (e.g., 'vendor')
        $users = User::where('role', $role)
                     ->latest()
                     ->paginate(10);

        return view('admin.users', compact('users', 'role'));
    }

    // Add to AdminController
    public function customerReport()
    {
        if (Auth::user()->role !== 'admin') abort(403);

        // Fetch only customers, include their orders and the items inside orders
        $customers = User::where('role', 'customer')
                        ->with('orders.items') // Eager load specifically for report
                        ->get();

        return view('admin.reports.customer_report', compact('customers'));
    }

    public function downloadCustomerReportPdf()
    {
        if (Auth::user()->role !== 'admin') abort(403);

        // 1. Fetch the exact same data
        $customers = User::where('role', 'customer')
                        ->with('orders.items')
                        ->get();

        // 2. Load the View into the PDF Engine

        $pdf = Pdf::loadView('admin.reports.customer_report', [
            'customers' => $customers,
            'isPdf' => true
        ]);
        // Optional: Set paper size (A4 is standard)
        $pdf->setPaper('a4', 'portrait');

        // 3. Return the download
        return $pdf->download('customer_sales_report.pdf');
    }

    // 1. Show the Report (Web View)
    public function vendorReport(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

            // Get search term from URL (e.g. ?search=John)
        $search = $request->input('search');

        // Pass search term to the helper
        $vendors = $this->getVendorData($search);

        return view('admin.reports.vendor_report', compact('vendors', 'search'));
    }

    // 2. Download PDF
    public function downloadVendorReportPdf(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $search = $request->input('search');
        $vendors = $this->getVendorData($search);

        $pdf = Pdf::loadView('admin.reports.vendor_report', [
            'vendors' => $vendors,
            'isPdf' => true,
            'search' => $search // Pass it so we can show "Searching for: X" in PDF header if you want
        ]);

        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('vendor_performance_report.pdf');
    }

    // Helper function to keep logic in one place
    private function getVendorData($search= null)
    {
        // Start Query
        $query = User::where('role', 'vendor')->with('products');

        // Apply Search Filter if it exists
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $vendors = $query->get();

        // Calculate sales and sort products (Same logic as before)
        foreach ($vendors as $vendor) {
            foreach ($vendor->products as $product) {
                $product->total_sold = OrderItem::where('product_name', $product->name)->sum('quantity');
            }
            $vendor->sorted_products = $vendor->products->sortByDesc('total_sold');
        }

        return $vendors;

    }


    /**
     * Display Trash Can (Soft Deleted Products).
     */
    public function trashedProducts()
    {
        if (Auth::user()->role !== 'admin') { abort(403); }

        // Fetch ONLY deleted items
        $trashedProducts = Product::onlyTrashed()->with('vendor')->paginate(10);

        return view('admin.products.trashed', compact('trashedProducts'));
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restoreProduct($id)
    {
        if (Auth::user()->role !== 'admin') { abort(403); }

        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore(); // <--- Brings it back!

        return redirect()->back()->with('success', 'Product restored successfully.');
    }

    /**
     * Permanently delete a product.
     */
    public function forceDeleteProduct($id)
    {
        if (Auth::user()->role !== 'admin') { abort(403); }

        $product = Product::onlyTrashed()->findOrFail($id);

        $productName = $product->name;
        $vendor = $product->vendor;

        // Delete image from storage if exists
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->forceDelete(); // <--- GONE FOREVER

        if ($vendor) {
            $vendor->notify(new ProductDeletedNotification($productName, 'Permanently Deleted'));
        }

        return redirect()->back()->with('success', 'Product permanently deleted.');
    }
}
