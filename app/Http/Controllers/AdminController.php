<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function users()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $users = User::paginate(10); // Pagination for better performance
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
}
