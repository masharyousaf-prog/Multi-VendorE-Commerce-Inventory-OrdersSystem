<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::delete('/cart/remove/{id}', [CartController::class, 'removeItem'])->name('cart.remove');

Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Vendor Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/vendor/dashboard', [VendorController::class, 'dashboard'])->name('vendor.dashboard');
    Route::get('/vendor/products/create', [VendorController::class, 'createProduct']);
    Route::post('/vendor/products', [VendorController::class, 'storeProduct']);
    Route::get('/vendor/products/{id}/edit', [VendorController::class, 'editProduct']);
    Route::put('/vendor/products/{id}', [VendorController::class, 'updateProduct']);

    Route::post('/vendor/notifications/read', function () {
    Auth::user()->unreadNotifications->markAsRead();
    return redirect()->back();
    })->middleware(['auth'])->name('vendor.notifications.read');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/console', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');

    Route::delete('/admin/products/{id}', [AdminController::class, 'deleteProduct'])
        ->name('admin.products.delete');

    Route::delete('/admin/products/{id}', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');
    Route::get('/admin/reports/customers', [AdminController::class, 'customerReport'])->name('admin.reports.customers');
    Route::get('/admin/reports/customers/download', [AdminController::class, 'downloadCustomerReportPdf'])
     ->name('admin.reports.customers.download');

    Route::get('/admin/reports/vendors', [AdminController::class, 'vendorReport'])->name('admin.reports.vendors');
    Route::get('/admin/reports/vendors/download', [AdminController::class, 'downloadVendorReportPdf'])->name('admin.reports.vendors.download');

    Route::get('/admin/products/trash', [App\Http\Controllers\AdminController::class, 'trashedProducts'])->name('admin.products.trash');
    Route::post('/admin/products/{id}/restore', [App\Http\Controllers\AdminController::class, 'restoreProduct'])->name('admin.products.restore');
    Route::delete('/admin/products/{id}/force-delete', [App\Http\Controllers\AdminController::class, 'forceDeleteProduct'])->name('admin.products.forceDelete');


    // Toggle Vendor/Customer Login Status
    Route::post('/admin/users/{id}/toggle', [AdminController::class, 'toggleStatus'])
        ->name('admin.users.toggle');
});
