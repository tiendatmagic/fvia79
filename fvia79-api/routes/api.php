<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
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
    Route::get('/faqs', ['App\Http\Controllers\FaqController', 'indexPublic']);
    Route::get('/deposit/rate', ['App\Http\Controllers\DepositController', 'getRate']);
    Route::get('/settings/public', ['App\Http\Controllers\SystemSettingController', 'publicSettings']);
    Route::get('/storefront/recent-purchases', ['App\Http\Controllers\StorefrontController', 'recentPurchases']);
    Route::get('/storefront/recent-deposits', ['App\Http\Controllers\StorefrontController', 'recentDeposits']);
    Route::get('/membership-tiers', ['App\Http\Controllers\AdminMembershipTierController', 'publicIndex']);
    Route::post('/tools/check-uid', ['App\Http\Controllers\CheckUidController', 'check'])->middleware('throttle:30,1');
    Route::post('/telegram/webhook', ['App\Http\Controllers\TelegramWebhookController', 'handleWebhook'])
        ->middleware('throttle:60,1');

    Route::middleware(['b2b.auth'])->group(function () {
        Route::get('/b2b/products', ['App\Http\Controllers\B2bController', 'index']);
        Route::get('/b2b/products/{id}', ['App\Http\Controllers\B2bController', 'show']);
        Route::post('/b2b/buy', ['App\Http\Controllers\B2bController', 'buy'])
            ->middleware('throttle:20,1');
        Route::get('/b2b/me', ['App\Http\Controllers\B2bController', 'me']);
        Route::get('/b2b/orders/{orderCode}', ['App\Http\Controllers\B2bController', 'showOrder']);
    });

    Route::middleware(['b2b.auth', 'admin'])->group(function () {
        Route::get('/admin/b2b/users', ['App\Http\Controllers\B2bAdminController', 'indexUsers']);
        Route::get('/admin/b2b/orders', ['App\Http\Controllers\B2bAdminController', 'indexOrders']);
        Route::post('/admin/b2b/products/{id}/inventory', ['App\Http\Controllers\B2bAdminController', 'importInventory'])
            ->middleware('throttle:10,1');
        Route::get('/admin/b2b/stats', ['App\Http\Controllers\B2bAdminController', 'getStats']);
    });

    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/auth/logout', ['App\Http\Controllers\AuthController', 'logout']);
        Route::get('/auth/me', ['App\Http\Controllers\AuthController', 'me']);
        Route::get('/storefront/mini-recent-purchases', ['App\Http\Controllers\StorefrontController', 'miniRecentPurchases'])
            ->middleware('throttle:30,1');

        Route::put('/profile', ['App\Http\Controllers\ProfileController', 'update']);
        Route::put('/profile/password', ['App\Http\Controllers\ProfileController', 'changePassword']);
        Route::post('/profile/2fa/enable', ['App\Http\Controllers\ProfileController', 'enable2Fa']);
        Route::post('/profile/2fa/confirm', ['App\Http\Controllers\ProfileController', 'confirm2Fa']);
        Route::post('/profile/2fa/disable', ['App\Http\Controllers\ProfileController', 'disable2Fa']);
        Route::post('/profile/api-key', ['App\Http\Controllers\ProfileController', 'generateApiKey']);
        Route::delete('/profile/api-key', ['App\Http\Controllers\ProfileController', 'revokeApiKey']);
        Route::get('/user/sessions', ['App\Http\Controllers\UserSessionController', 'index']);
        Route::delete('/user/sessions/others', ['App\Http\Controllers\UserSessionController', 'destroyOthers']);
        Route::delete('/user/sessions/{id}', ['App\Http\Controllers\UserSessionController', 'destroy']);
        Route::get('/user/telegram', ['App\Http\Controllers\UserTelegramController', 'getStatus']);
        Route::post('/user/telegram/link-token', ['App\Http\Controllers\UserTelegramController', 'createLinkToken'])
            ->middleware('throttle:5,1');
        Route::post('/user/telegram', ['App\Http\Controllers\UserTelegramController', 'updateStatus']);
        Route::post('/user/telegram/test', ['App\Http\Controllers\UserTelegramController', 'sendTestMessage']);
        Route::delete('/user/telegram', ['App\Http\Controllers\UserTelegramController', 'disconnect']);

        Route::post('/orders', ['App\Http\Controllers\OrderController', 'store']);
        Route::get('/orders', ['App\Http\Controllers\OrderController', 'index']);
        Route::get('/orders/{id}', ['App\Http\Controllers\OrderController', 'show']);
        Route::post('/coupons/validate', ['App\Http\Controllers\CouponController', 'validateCoupon']);

        Route::post('/deposit', ['App\Http\Controllers\DepositController', 'create']);
        Route::get('/deposit/unseen-success', ['App\Http\Controllers\DepositController', 'getUnseenSuccessDeposit']);
        Route::post('/deposit/mark-seen/{id}', ['App\Http\Controllers\DepositController', 'markDepositSeen']);
        Route::get('/deposit/history', ['App\Http\Controllers\DepositController', 'history']);
        Route::get('/deposit/invoice/{id}', ['App\Http\Controllers\DepositController', 'showInvoice']);
        Route::post('/deposit/invoice/{id}/cancel', ['App\Http\Controllers\DepositController', 'cancelInvoice']);
        Route::post('/deposit/invoice/{id}/check', ['App\Http\Controllers\DepositController', 'checkTransaction'])->middleware('throttle:20,1');

        Route::middleware(['admin'])->group(function () {
            Route::get('/admin/dashboard-stats', ['App\Http\Controllers\DashboardController', 'stats']);

            Route::get('/admin/settings', ['App\Http\Controllers\SystemSettingController', 'index']);
            Route::put('/admin/settings', ['App\Http\Controllers\SystemSettingController', 'update']);
            Route::post('/admin/settings/upload-binance-qr', ['App\Http\Controllers\SystemSettingController', 'uploadBinanceQr']);
            Route::post('/admin/settings/telegram/test', ['App\Http\Controllers\SystemSettingController', 'testTelegramBot']);
            Route::get('/admin/database-backup/status', ['App\Http\Controllers\DatabaseBackupController', 'status']);
            Route::post('/admin/database-backup/export', ['App\Http\Controllers\DatabaseBackupController', 'export'])
                ->middleware('throttle:database-backup-export');
            Route::post('/admin/database-backup/restore', ['App\Http\Controllers\DatabaseBackupController', 'restore'])
                ->middleware('throttle:database-backup-restore');
            Route::get('/admin/suppliers/templates', ['App\Http\Controllers\SupplierController', 'templates']);
            Route::get('/admin/suppliers', ['App\Http\Controllers\SupplierController', 'index']);
            Route::post('/admin/suppliers', ['App\Http\Controllers\SupplierController', 'store']);
            Route::put('/admin/suppliers/{code}', ['App\Http\Controllers\SupplierController', 'update']);
            Route::post('/admin/suppliers/{code}/balance', ['App\Http\Controllers\SupplierController', 'balance'])
                ->middleware('throttle:10,1');
            Route::post('/admin/suppliers/{code}/sync', ['App\Http\Controllers\SupplierController', 'sync']);
            Route::post('/admin/suppliers/{code}/activate-in-stock-products', ['App\Http\Controllers\SupplierController', 'activateInStockProducts']);
            Route::post('/admin/suppliers/{code}/deactivate-out-of-stock-products', ['App\Http\Controllers\SupplierController', 'deactivateOutOfStockProducts']);
            Route::post('/admin/suppliers/{code}/deactivate-out-of-stock-categories', ['App\Http\Controllers\SupplierController', 'deactivateOutOfStockCategories']);
            Route::post('/admin/maintenance/clear-views', ['App\Http\Controllers\SystemSettingController', 'clearViews']);
            Route::post('/admin/maintenance/clear-config', ['App\Http\Controllers\SystemSettingController', 'clearConfig']);
            Route::post('/admin/maintenance/clear-routes', ['App\Http\Controllers\SystemSettingController', 'clearRoutes']);
            Route::post('/admin/maintenance/optimize', ['App\Http\Controllers\SystemSettingController', 'optimize']);
            Route::post('/admin/maintenance/clear-optimize', ['App\Http\Controllers\SystemSettingController', 'clearOptimize']);
            Route::post('/admin/maintenance/clear-all', ['App\Http\Controllers\SystemSettingController', 'clearAllCaches']);
            Route::post('/admin/maintenance/recalculate-profit', ['App\Http\Controllers\SystemSettingController', 'recalculateProfit']);
            Route::post('/admin/maintenance/reset-profit', ['App\Http\Controllers\SystemSettingController', 'resetProfit']);
            Route::post('/admin/maintenance/clean-tokens', ['App\Http\Controllers\SystemSettingController', 'cleanTokens']);
            Route::post('/admin/maintenance/clean-all-tokens', ['App\Http\Controllers\SystemSettingController', 'cleanAllTokens']);

            Route::get('/admin/coupons', ['App\Http\Controllers\AdminCouponController', 'index']);
            Route::post('/admin/coupons', ['App\Http\Controllers\AdminCouponController', 'store']);
            Route::get('/admin/coupons/{id}', ['App\Http\Controllers\AdminCouponController', 'show']);
            Route::put('/admin/coupons/{id}', ['App\Http\Controllers\AdminCouponController', 'update']);
            Route::delete('/admin/coupons/{id}', ['App\Http\Controllers\AdminCouponController', 'destroy']);
            Route::post('/admin/coupons/{id}/restore', ['App\Http\Controllers\AdminCouponController', 'restore']);

            Route::get('/admin/users', ['App\Http\Controllers\AdminUserController', 'index']);
            Route::post('/admin/users', ['App\Http\Controllers\AdminUserController', 'store']);
            Route::post('/admin/users/bulk-status', ['App\Http\Controllers\AdminUserController', 'bulkStatus']);
            Route::post('/admin/users/bulk-delete', ['App\Http\Controllers\AdminUserController', 'bulkDestroy']);
            Route::put('/admin/users/{id}', ['App\Http\Controllers\AdminUserController', 'update']);
            Route::post('/admin/users/{id}/adjust-balance', ['App\Http\Controllers\AdminUserController', 'adjustBalance']);
            Route::post('/admin/users/{id}/reset-2fa', ['App\Http\Controllers\AdminUserController', 'reset2fa']);
            Route::post('/admin/users/{id}/enable-2fa', ['App\Http\Controllers\AdminUserController', 'enable2fa']);
            Route::delete('/admin/users/{id}', ['App\Http\Controllers\AdminUserController', 'destroy']);

            Route::middleware(['membership_tiers.enabled'])->group(function () {
                Route::post('/admin/users/{id}/adjust-cumulative-deposit', ['App\Http\Controllers\AdminUserController', 'adjustCumulativeDeposit']);
                Route::get('/admin/membership-tiers', ['App\Http\Controllers\AdminMembershipTierController', 'index']);
                Route::post('/admin/membership-tiers', ['App\Http\Controllers\AdminMembershipTierController', 'store']);
                Route::put('/admin/membership-tiers/{id}', ['App\Http\Controllers\AdminMembershipTierController', 'update']);
                Route::delete('/admin/membership-tiers/{id}', ['App\Http\Controllers\AdminMembershipTierController', 'destroy']);
            });

            Route::post('/products', ['App\Http\Controllers\ProductController', 'store']);
            Route::put('/products/{id}', ['App\Http\Controllers\ProductController', 'update']);
            Route::delete('/products/{id}', ['App\Http\Controllers\ProductController', 'destroy']);
            Route::post('/products/{id}/restore', ['App\Http\Controllers\ProductController', 'restore']);

            Route::post('/categories', ['App\Http\Controllers\CategoryController', 'store']);
            Route::post('/categories/reorder', ['App\Http\Controllers\CategoryController', 'reorder']);
            Route::post('/categories/deactivate-out-of-stock', ['App\Http\Controllers\CategoryController', 'deactivateOutOfStockCategories']);
            Route::put('/categories/{id}', ['App\Http\Controllers\CategoryController', 'update']);
            Route::delete('/categories/{id}', ['App\Http\Controllers\CategoryController', 'destroy']);

            Route::post('/inventory/import', ['App\Http\Controllers\InventoryController', 'import']);
            Route::get('/inventory/items', ['App\Http\Controllers\InventoryController', 'index']);
            Route::get('/inventory/items/{id}', ['App\Http\Controllers\InventoryController', 'show']);
            Route::put('/inventory/items/{id}', ['App\Http\Controllers\InventoryController', 'update']);
            Route::delete('/inventory/items/{id}', ['App\Http\Controllers\InventoryController', 'destroy']);

            Route::get('/inventory/resource-items', ['App\Http\Controllers\InventoryController', 'listResourceItems']);
            Route::post('/inventory/resource-items/check-live', ['App\Http\Controllers\InventoryController', 'checkResourceItemsLive']);
            Route::post('/inventory/resource-items/bulk-status', ['App\Http\Controllers\InventoryController', 'bulkUpdateResourceItemStatus']);
            Route::post('/inventory/resource-items/bulk-delete', ['App\Http\Controllers\InventoryController', 'bulkDestroyResourceItems']);
            Route::put('/inventory/resource-items/{id}', ['App\Http\Controllers\InventoryController', 'updateResourceItem']);
            Route::delete('/inventory/resource-items/{id}', ['App\Http\Controllers\InventoryController', 'destroyResourceItem']);

            Route::get('/admin/deposits', ['App\Http\Controllers\AdminDepositController', 'index']);
            Route::post('/admin/deposits/bulk-revoke', ['App\Http\Controllers\AdminDepositController', 'bulkRevoke']);
            Route::post('/admin/deposits/{id}/approve', ['App\Http\Controllers\AdminDepositController', 'approve']);
            Route::post('/admin/deposits/{id}/reject', ['App\Http\Controllers\AdminDepositController', 'reject']);
            Route::post('/admin/deposits/{id}/revoke', ['App\Http\Controllers\AdminDepositController', 'revoke']);

            Route::get('/admin/faqs', ['App\Http\Controllers\FaqController', 'indexAdmin']);
            Route::post('/admin/policy-categories', ['App\Http\Controllers\FaqController', 'storePolicyCategory']);
            Route::put('/admin/policy-categories/{id}', ['App\Http\Controllers\FaqController', 'updatePolicyCategory']);
            Route::delete('/admin/policy-categories/{id}', ['App\Http\Controllers\FaqController', 'destroyPolicyCategory']);
            Route::post('/admin/warranty-policies', ['App\Http\Controllers\FaqController', 'storePolicy']);
            Route::put('/admin/warranty-policies/{id}', ['App\Http\Controllers\FaqController', 'updatePolicy']);
            Route::delete('/admin/warranty-policies/{id}', ['App\Http\Controllers\FaqController', 'destroyPolicy']);
            Route::post('/admin/faqs', ['App\Http\Controllers\FaqController', 'storeFaq']);
            Route::put('/admin/faqs/{id}', ['App\Http\Controllers\FaqController', 'updateFaq']);
            Route::delete('/admin/faqs/{id}', ['App\Http\Controllers\FaqController', 'destroyFaq']);
        });
    });
});
