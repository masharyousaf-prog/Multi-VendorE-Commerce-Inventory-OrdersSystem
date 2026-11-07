<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display the Vendor Dashboard.
     */
    public function dashboard()
    {
        if (Auth::user()->role !== 'vendor') {
            abort(403);
        }

        $vendorId = Auth::id();
        $query = Product::where('user_id', $vendorId);
        // Get products specific to THIS vendor
        // $myProducts = Product::where('user_id', $vendorId)->get();
        $allProducts = $query->get();
        // Total stock value
        // $totalStockValue = Product::where('user_id', $vendorId)
        //                    ->sum(DB::raw('price * stock'));
        $totalStockValue = $allProducts->sum(function ($product) {
            return $product->price * $product->stock;
        });
        // PREPARE CHART DATA (New)
        // We need two simple arrays: one for labels ("Shirt", "Shoes") and one for numbers (10, 5)
        $chartLabels = $allProducts->pluck('name'); // List of names
        $chartData = $allProducts->pluck('stock'); // List of stock quantities

        $myProducts = Product::where('user_id', $vendorId)
            ->latest()
            ->paginate(10);

        $deletedProducts = Product::onlyTrashed()
            ->where('user_id', $vendorId)
            ->latest()
            ->get();

        $notifications = Auth::user()->unreadNotifications;
        // return view('vendor.dashboard', compact('myProducts', 'totalStockValue','chartLabels','chartData'));
        return view('vendor.dashboard', compact('myProducts', 'totalStockValue', 'chartLabels', 'chartData','notifications'));
    }

    /**
     * Show form to create a new product.
     */
    public function createProduct()
    {
        if (Auth::user()->role !== 'vendor') {
            abort(403);
        }

        return view('vendor.products.create');
    }

    /**
     * Store a new product in the database.
     */
    public function storeProduct(Request $request)
    {
        // 1. Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0|max:99',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        // 2. Handle Image Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Stores in storage/app/public/products
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // 3. Create Product
        Product::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'discount' => $request->discount ?? 0,
            'description' => $request->description,
            'image' => $imagePath, // Save the path (e.g., "products/filename.jpg")
        ]);

        return redirect()->route('vendor.dashboard')->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function editProduct($id)
    {
        // Find product and ensure it belongs to the logged-in vendor
        $product = Product::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        return view('vendor.products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'discount' => 'required|integer|min:0|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        // Handle Image Upload (only if a new one is uploaded)
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        // Update fields
        $product->name = $request->name;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->description = $request->description;
        $product->discount = $request->discount;
        $product->save();

        return redirect()->route('vendor.dashboard')->with('success', 'Product updated successfully!');
    }
}
