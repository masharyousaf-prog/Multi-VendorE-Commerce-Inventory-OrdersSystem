<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products (Catalog).
     */
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            // Filter where Name OR Description contains the search text
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        // Fetch all products, usually paginated
        //$products = Product::where('stock', '>', 0)->paginate(12);
        $products = $query->latest()->paginate(9);
        return view('products.index', compact('products'));
    }

    /**
     * Display the specified product details.
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }
}
