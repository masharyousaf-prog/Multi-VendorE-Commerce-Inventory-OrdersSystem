<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

// Public Storefront & Discovery Routes
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/store/{slug}', [ProductController::class, 'vendorStore'])->name('vendor.store');

// Guest Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Customer Routes (JWT Protected)
Route::middleware(['jwt.auth:customer,vendor,admin'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // New Marketplace Features: Wishlist & Verified Reviews
    Route::post('/products/{product}/wishlist', [ProductController::class, 'toggleWishlist'])->name('products.wishlist');
    Route::post('/products/{product}/reviews', [ProductController::class, 'storeReview'])->name('products.reviews.store');
});

// Vendor Seller Hub Routes (JWT Role: vendor)
Route::prefix('vendor')->name('vendor.')->middleware(['jwt.auth:vendor'])->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
    Route::get('/products/create', [VendorController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [VendorController::class, 'storeProduct'])->name('products.store');
});

// Admin Command Routes (JWT Role: admin — Notification routes removed)
Route::prefix('admin')->name('admin.')->middleware(['jwt.auth:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('products.delete');
    Route::get('/products/trashed', [AdminController::class, 'trashedProducts'])->name('products.trashed');
    Route::post('/products/{id}/restore', [AdminController::class, 'restoreProduct'])->name('products.restore');
    Route::delete('/products/{id}/force', [AdminController::class, 'forceDeleteProduct'])->name('products.forceDelete');
    Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');
});
