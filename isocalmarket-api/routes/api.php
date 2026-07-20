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

    Route::get('/products/grouped', ['App\Http\Controllers\ProductController', 'indexGrouped']);
    Route::get('/products', ['App\Http\Controllers\ProductController', 'index']);
    Route::get('/products/{id}', ['App\Http\Controllers\ProductController', 'show']);
    Route::get('/categories', ['App\Http\Controllers\CategoryController', 'index']);

    // B2B API Routes - protected by B2B API Key middleware
    Route::middleware(['b2b.auth'])->group(function () {
        Route::get('/b2b/products', ['App\Http\Controllers\B2bController', 'index']);
        Route::get('/b2b/products/{id}', ['App\Http\Controllers\B2bController', 'show']);
        Route::post('/b2b/buy', ['App\Http\Controllers\B2bController', 'buy']);
        Route::get('/b2b/me', ['App\Http\Controllers\B2bController', 'me']);
    });

    // B2B Admin API Routes - protected by B2B API Key auth AND Admin middleware
    Route::middleware(['b2b.auth', 'admin'])->group(function () {
        Route::get('/admin/b2b/users', ['App\Http\Controllers\B2bAdminController', 'indexUsers']);
        Route::get('/admin/b2b/orders', ['App\Http\Controllers\B2bAdminController', 'indexOrders']);
        Route::post('/admin/b2b/products/{id}/inventory', ['App\Http\Controllers\B2bAdminController', 'importInventory']);
        Route::get('/admin/b2b/stats', ['App\Http\Controllers\B2bAdminController', 'getStats']);
    });

    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/auth/logout', ['App\Http\Controllers\AuthController', 'logout']);
        Route::get('/auth/me', ['App\Http\Controllers\AuthController', 'me']);

        // Profile routes
        Route::put('/profile', ['App\Http\Controllers\ProfileController', 'update']);
        Route::put('/profile/password', ['App\Http\Controllers\ProfileController', 'changePassword']);
        Route::post('/profile/2fa/enable', ['App\Http\Controllers\ProfileController', 'enable2Fa']);
        Route::post('/profile/2fa/confirm', ['App\Http\Controllers\ProfileController', 'confirm2Fa']);
        Route::post('/profile/2fa/disable', ['App\Http\Controllers\ProfileController', 'disable2Fa']);
        Route::post('/profile/api-key', ['App\Http\Controllers\ProfileController', 'generateApiKey']);

        Route::post('/orders', ['App\Http\Controllers\OrderController', 'store']);
        Route::get('/orders', ['App\Http\Controllers\OrderController', 'index']);
        Route::get('/orders/{id}', ['App\Http\Controllers\OrderController', 'show']);
        Route::post('/coupons/validate', ['App\Http\Controllers\CouponController', 'validateCoupon']);

        // Deposit routes
        Route::get('/deposit/rate', ['App\Http\Controllers\DepositController', 'getRate']);
        Route::post('/deposit', ['App\Http\Controllers\DepositController', 'create']);
        Route::get('/deposit/history', ['App\Http\Controllers\DepositController', 'history']);
        Route::get('/deposit/invoice/{id}', ['App\Http\Controllers\DepositController', 'showInvoice']);
        Route::post('/deposit/invoice/{id}/cancel', ['App\Http\Controllers\DepositController', 'cancelInvoice']);
        Route::post('/deposit/invoice/{id}/check', ['App\Http\Controllers\DepositController', 'checkTransaction'])->middleware('throttle:10,1');

        Route::middleware(['admin'])->group(function () {
            Route::get('/admin/dashboard-stats', ['App\Http\Controllers\DashboardController', 'stats']);

            Route::get('/admin/settings', ['App\Http\Controllers\SystemSettingController', 'index']);
            Route::put('/admin/settings', ['App\Http\Controllers\SystemSettingController', 'update']);
            Route::post('/admin/maintenance/clear-views', ['App\Http\Controllers\SystemSettingController', 'clearViews']);
            Route::post('/admin/maintenance/clear-config', ['App\Http\Controllers\SystemSettingController', 'clearConfig']);
            Route::post('/admin/maintenance/clear-routes', ['App\Http\Controllers\SystemSettingController', 'clearRoutes']);
            Route::post('/admin/maintenance/optimize', ['App\Http\Controllers\SystemSettingController', 'optimize']);
            Route::post('/admin/maintenance/clear-optimize', ['App\Http\Controllers\SystemSettingController', 'clearOptimize']);
            Route::post('/admin/maintenance/clear-all', ['App\Http\Controllers\SystemSettingController', 'clearAllCaches']);
            Route::post('/admin/maintenance/clean-tokens', ['App\Http\Controllers\SystemSettingController', 'cleanTokens']);
            Route::post('/admin/maintenance/clean-all-tokens', ['App\Http\Controllers\SystemSettingController', 'cleanAllTokens']);

            // Admin Coupon Management (CRUD)
            Route::get('/admin/coupons', ['App\Http\Controllers\AdminCouponController', 'index']);
            Route::post('/admin/coupons', ['App\Http\Controllers\AdminCouponController', 'store']);
            Route::get('/admin/coupons/{id}', ['App\Http\Controllers\AdminCouponController', 'show']);
            Route::put('/admin/coupons/{id}', ['App\Http\Controllers\AdminCouponController', 'update']);
            Route::delete('/admin/coupons/{id}', ['App\Http\Controllers\AdminCouponController', 'destroy']);
            Route::post('/admin/coupons/{id}/restore', ['App\Http\Controllers\AdminCouponController', 'restore']);

            // Admin Member Management (CRUD & Balance Adjustment)
            Route::get('/admin/users', ['App\Http\Controllers\AdminUserController', 'index']);
            Route::post('/admin/users', ['App\Http\Controllers\AdminUserController', 'store']);
            Route::put('/admin/users/{id}', ['App\Http\Controllers\AdminUserController', 'update']);
            Route::post('/admin/users/{id}/adjust-balance', ['App\Http\Controllers\AdminUserController', 'adjustBalance']);
            Route::post('/admin/users/{id}/reset-2fa', ['App\Http\Controllers\AdminUserController', 'reset2fa']);
            Route::post('/admin/users/{id}/enable-2fa', ['App\Http\Controllers\AdminUserController', 'enable2fa']);
            Route::delete('/admin/users/{id}', ['App\Http\Controllers\AdminUserController', 'destroy']);

            Route::post('/products', ['App\Http\Controllers\ProductController', 'store']);
            Route::put('/products/{id}', ['App\Http\Controllers\ProductController', 'update']);
            Route::delete('/products/{id}', ['App\Http\Controllers\ProductController', 'destroy']);
            Route::post('/products/{id}/restore', ['App\Http\Controllers\ProductController', 'restore']);

            Route::post('/categories', ['App\Http\Controllers\CategoryController', 'store']);
            Route::put('/categories/{id}', ['App\Http\Controllers\CategoryController', 'update']);
            Route::delete('/categories/{id}', ['App\Http\Controllers\CategoryController', 'destroy']);

            Route::post('/inventory/import', ['App\Http\Controllers\InventoryController', 'import']);
            Route::get('/inventory/items', ['App\Http\Controllers\InventoryController', 'index']);
            Route::get('/inventory/items/{id}', ['App\Http\Controllers\InventoryController', 'show']);
            Route::put('/inventory/items/{id}', ['App\Http\Controllers\InventoryController', 'update']);
            Route::delete('/inventory/items/{id}', ['App\Http\Controllers\InventoryController', 'destroy']);

            // Admin Deposit Management
            Route::get('/admin/deposits', ['App\Http\Controllers\AdminDepositController', 'index']);
            Route::post('/admin/deposits/{id}/approve', ['App\Http\Controllers\AdminDepositController', 'approve']);
            Route::post('/admin/deposits/{id}/reject', ['App\Http\Controllers\AdminDepositController', 'reject']);
        });
    });
});
