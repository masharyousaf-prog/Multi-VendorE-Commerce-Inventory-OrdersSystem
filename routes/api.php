<?php

use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public JWT Auth & Marketplace Discovery API Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/flash-deals', [ProductApiController::class, 'flashDeals']);
Route::get('/products/{product}', [ProductApiController::class, 'show']);
Route::get('/categories', [ProductApiController::class, 'categories']);

/*
|--------------------------------------------------------------------------
| Protected Stateless JWT API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/me', fn (Request $request) => response()->json([
        'status' => 'success',
        'user' => auth()->user(),
    ]));
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Vendor & Admin Product Management via JWT API
    Route::middleware(['jwt.auth:vendor,admin'])->group(function () {
        Route::post('/products', [ProductApiController::class, 'store']);
        Route::put('/products/{product}', [ProductApiController::class, 'update']);
        Route::delete('/products/{product}', [ProductApiController::class, 'destroy']);
    });
});
