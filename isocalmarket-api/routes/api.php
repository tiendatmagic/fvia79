<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth routes — rate limit nghiêm ngặt chống brute-force (10 req/phút)
    Route::middleware(['throttle:auth'])->group(function () {
        Route::post('/auth/register', ['App\Http\Controllers\AuthController', 'register']);
        Route::post('/auth/login', ['App\Http\Controllers\AuthController', 'login']);
        Route::post('/auth/login/2fa', ['App\Http\Controllers\AuthController', 'login2Fa']);
    });

    Route::post('/auth/refresh', ['App\Http\Controllers\AuthController', 'refresh']);

    Route::get('/products', ['App\Http\Controllers\ProductController', 'index']);
    Route::get('/products/{id}', ['App\Http\Controllers\ProductController', 'show']);
    Route::get('/categories', ['App\Http\Controllers\CategoryController', 'index']);

    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/auth/logout', ['App\Http\Controllers\AuthController', 'logout']);
        Route::get('/auth/me', ['App\Http\Controllers\AuthController', 'me']);

        // Profile routes
        Route::put('/profile', ['App\Http\Controllers\ProfileController', 'update']);
        Route::put('/profile/password', ['App\Http\Controllers\ProfileController', 'changePassword']);
        Route::post('/profile/2fa/enable', ['App\Http\Controllers\ProfileController', 'enable2Fa']);
        Route::post('/profile/2fa/confirm', ['App\Http\Controllers\ProfileController', 'confirm2Fa']);
        Route::post('/profile/2fa/disable', ['App\Http\Controllers\ProfileController', 'disable2Fa']);

        Route::post('/orders', ['App\Http\Controllers\OrderController', 'store']);
        Route::get('/orders', ['App\Http\Controllers\OrderController', 'index']);
        Route::get('/orders/{id}', ['App\Http\Controllers\OrderController', 'show']);

        Route::middleware(['admin'])->group(function () {
            Route::get('/admin/dashboard-stats', ['App\Http\Controllers\DashboardController', 'stats']);

            Route::post('/products', ['App\Http\Controllers\ProductController', 'store']);
            Route::put('/products/{id}', ['App\Http\Controllers\ProductController', 'update']);
            Route::delete('/products/{id}', ['App\Http\Controllers\ProductController', 'destroy']);

            Route::post('/inventory/import', ['App\Http\Controllers\InventoryController', 'import']);
            Route::get('/inventory/items', ['App\Http\Controllers\InventoryController', 'index']);
            Route::delete('/inventory/items/{id}', ['App\Http\Controllers\InventoryController', 'destroy']);
        });
    });
});
