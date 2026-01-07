<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\GeneralController;

Route::get('/health', function (Request $request) {
    return response()->json(['status' => 'OK'], 200);
});
Route::get('/', [GeneralController::class, 'home'])->name('home');
Route::get('/products', [GeneralController::class, 'products'])->name('products');
Route::get('/products/{id?}', [GeneralController::class, 'product'])->name('product');
Route::get('/order-check', [GeneralController::class, 'orderCheck'])->name('order-check');
Route::get('/checkout', [GeneralController::class, 'checkout'])->name('checkout');
Route::post('/checkout-payment', [GeneralController::class, 'checkoutPayment'])->name('checkout-payment');
Route::get('/{slug?}', [GeneralController::class, 'single'])->name('single');
Route::post('/reviews/submit', [GeneralController::class, 'submitReview'])->name('reviews.submit');
Route::get('/storage/{filename}', [GeneralController::class, 'productImage'])->name('product.image');