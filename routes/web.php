<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Serve Local Product Images Reliably from storage/app/public
Route::get('/media/{path}', function (string $path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*')->name('media.serve');

// Public Storefront, Vendor Store & Live Order Tracking Routes
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/store/{slug}', [ProductController::class, 'vendorStore'])->name('vendor.store');
Route::get('/track-order', [CartController::class, 'trackOrder'])->name('orders.track');

// Guest Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Customer-Only Routes: Cart, Checkout, Order History, Wishlist & Reviews
Route::middleware(['jwt.auth:customer'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::match(['get', 'post'], '/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Customer Order Management
    Route::get('/orders', [CartController::class, 'myOrders'])->name('orders.my');
    Route::patch('/orders/{order}/cancel', [CartController::class, 'cancelOrder'])->name('orders.cancel');

    // Customer Wishlist & Reviews
    Route::get('/wishlist', [ProductController::class, 'wishlistIndex'])->name('wishlist.index');
    Route::post('/products/{product}/wishlist', [ProductController::class, 'toggleWishlist'])->name('products.wishlist');
    Route::post('/products/{product}/reviews', [ProductController::class, 'storeReview'])->name('products.reviews.store');
});

// Vendor Seller Hub Routes (Role: vendor)
Route::prefix('vendor')->name('vendor.')->middleware(['jwt.auth:vendor'])->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
    Route::put('/settings', [VendorController::class, 'updateStoreSettings'])->name('settings.update');

    // Vendor Order Fulfillment Module
    Route::get('/orders', [VendorController::class, 'orders'])->name('orders');
    Route::patch('/orders/{orderItem}/status', [VendorController::class, 'updateOrderStatus'])->name('orders.status');

    // Vendor Product Studio
    Route::get('/products', [VendorController::class, 'products'])->name('products.index');
    Route::get('/products/create', [VendorController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [VendorController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [VendorController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{product}', [VendorController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [VendorController::class, 'deleteProduct'])->name('products.delete');
});

// Admin Command Routes (Role: admin)
Route::prefix('admin')->name('admin.')->middleware(['jwt.auth:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');

    // Admin Global Order Management
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');

    // Admin Promo Coupons Management
    Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons');
    Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');
    Route::patch('/coupons/{coupon}/toggle', [AdminController::class, 'toggleCoupon'])->name('coupons.toggle');
    Route::delete('/coupons/{coupon}', [AdminController::class, 'deleteCoupon'])->name('coupons.delete');

    // Admin Products & Trash Vault
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('products.delete');
    Route::get('/products/trashed', [AdminController::class, 'trashedProducts'])->name('products.trashed');
    Route::post('/products/{id}/restore', [AdminController::class, 'restoreProduct'])->name('products.restore');
    Route::delete('/products/{id}/force', [AdminController::class, 'forceDeleteProduct'])->name('products.forceDelete');

    // PDF & Audit Reports
    Route::get('/reports/customers', [AdminController::class, 'customerReport'])->name('reports.customers');
    Route::get('/reports/vendors', [AdminController::class, 'vendorReport'])->name('reports.vendors');
});
