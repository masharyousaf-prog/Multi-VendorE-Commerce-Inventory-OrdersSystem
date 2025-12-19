<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Display the Admin Console / Dashboard.
     */
    public function dashboard()
    {
        // Security Check (Best done in Middleware, but shown here for logic clarity)
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Gather system-wide statistics
        $stats = [
            'total_users'    => User::count(),
            'total_vendors'  => User::where('role', 'vendor')->count(),
            'total_products' => Product::count(),
            'recent_users'   => User::latest()->take(5)->get()
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Manage Users (e.g., View all users).
     */
    public function users(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        // 1. Start the query
        $query = User::query();

        // 2. Check for 'role' parameter in the URL (e.g., ?role=vendor)
        if ($request->has('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        // 3. Get results with pagination
        $users = $query->latest()->paginate(10);; // Pagination for better performance
        return view('admin.users', compact('users'));
    }

    public function products()
    {
        // specific admin check
        if (Auth::user()->role !== 'admin') { abort(403); }

        // 'with' eager loads the vendor data to avoid N+1 query performance issues
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

        // Optional: Delete the image file to save server space
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->back()->with('success', 'Product removed successfully.');
    }

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
}
