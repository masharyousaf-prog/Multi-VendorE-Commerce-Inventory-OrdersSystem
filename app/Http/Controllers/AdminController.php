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
    public function users()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $users = User::paginate(10);
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

        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

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
        ]);
    }
}
