<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\OrderItemController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/categories', CategoryController::class);

Route::apiResource('/suppliers', SupplierController::class);

Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('orders/status/{status}', [OrderController::class, 'getByStatus']);
    
    // Product management
    Route::apiResource('products', ProductController::class);
    Route::patch('products/{id}/remove-image', [ProductController::class, 'removeImage']);
    Route::get('products/featured', [ProductController::class, 'getFeatured']);
    Route::get('products/category/{categoryId}', [ProductController::class, 'getByCategory']);

    Route::prefix('order-items')->group(function () {
        Route::get('/', [OrderItemController::class, 'index']);
        Route::post('/', [OrderItemController::class, 'store']);
        Route::get('/{id}', [OrderItemController::class, 'show']);
        Route::put('/{id}', [OrderItemController::class, 'update']);
        Route::delete('/{id}', [OrderItemController::class, 'destroy']);
        Route::get('/order/{orderId}', [OrderItemController::class, 'getByOrder']);
        Route::patch('/{id}/quantity', [OrderItemController::class, 'updateQuantity']);
        Route::post('/bulk', [OrderItemController::class, 'bulkAdd']);
    });