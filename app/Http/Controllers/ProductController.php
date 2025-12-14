<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products (Catalog).
     */
    public function index()
    {
        // Fetch all products, usually paginated
        $products = Product::where('stock', '>', 0)->paginate(12);
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
