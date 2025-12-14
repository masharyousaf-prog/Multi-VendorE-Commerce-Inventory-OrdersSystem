<?php

use App\Http\Controllers\Api\ProductApiController;
use Illuminate\Support\Facades\Route;

// This single line creates all 5 routes mapping to your controller
Route::apiResource('products', ProductApiController::class);
