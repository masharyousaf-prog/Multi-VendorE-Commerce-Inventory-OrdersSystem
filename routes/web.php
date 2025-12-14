<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [ProductController::class, 'index']);
Route::get('/cart', [CartController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']); // To view single product
Route::post('/cart/add', [CartController::class, 'addToCart']);   // To add items to cart
// Show Checkout Page
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
// Process the Order (Place Order)
Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
// THIS IS THE MISSING LINE:
Route::delete('/cart/remove/{id}', [CartController::class, 'removeItem'])->name('cart.remove');

// Vendor Routes (Protected)

    Route::get('/vendor/dashboard', [VendorController::class, 'dashboard'])->name('vendor.dashboard');
    Route::get('/vendor/products/create', [VendorController::class, 'createProduct']);
    Route::post('/vendor/products', [VendorController::class, 'storeProduct']);
    Route::get('/vendor/products/{id}/edit', [VendorController::class, 'editProduct']);
    Route::put('/vendor/products/{id}', [VendorController::class, 'updateProduct']);


// Admin Routes (Protected)

    Route::get('/admin/console', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');

// --- AUTHENTICATION ROUTES ---

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Register
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
