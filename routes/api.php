<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Api\SingleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;

Route::middleware('auth.bearer')->group(function () {
    Route::get('/', function (Request $request) {
        return response()->json(['message' => 'Authorized']);
    });

    Route::apiResource('/singles', SingleController::class);
    Route::apiResource('/categories', CategoryController::class);
    Route::apiResource('/products', ProductController::class);
    Route::apiResource('/orders', OrderController::class);
    Route::apiResource('/reviews', ReviewController::class);
});

Route::post('/orders/checkout', [OrderController::class, 'checkout']);
Route::post('/reviews/submit', [ReviewController::class, 'submit']);