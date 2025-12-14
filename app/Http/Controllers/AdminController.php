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

    
}
