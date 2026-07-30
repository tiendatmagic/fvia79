<?php

use App\Application\Services\DepositService;
use App\Application\Services\OrderService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\UserSessionModel;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('auth:force-logout-all', function () {
    $this->info('Starting force logout of all users...');

    $affected = UserModel::query()->increment('token_version', 1, [
        'refresh_token_hash' => null,
        'refresh_token_expired_at' => null,
    ]);
    UserSessionModel::query()->delete();
    $this->comment("Cleared refresh tokens for {$affected} users in database.");

    $this->info('Successfully logged out all users and invalidated all active tokens!');
})->purpose('Force logout all users by clearing refresh tokens and incrementing their token version');

Artisan::command('deposit:check-pending', function (DepositService $depositService) {
    $this->info('Starting checks for pending deposits...');

    $depositService->checkAllPendingDeposits(function (string $type, string $message) {
        if ($type === 'error') {
            $this->error($message);
        } else {
            $this->info($message);
        }
    });

    $this->info('Pending deposits check completed.');
})->purpose('Scan blockchain and reconcile pending USDT BEP20 deposits');

Artisan::command('suppliers:reconcile-orders', function (OrderService $orderService) {
    $results = $orderService->reconcilePendingSupplierOrders();
    $this->info('Supplier order reconciliation completed: '.count($results).' order(s) checked.');
})->purpose('Retrieve pending supplier order resources and finalize local orders');

Schedule::command('deposit:check-pending')->everyMinute();
Schedule::command('suppliers:reconcile-orders')->everyMinute()->withoutOverlapping();
Schedule::command('suppliers:sync --due')->everyMinute()->withoutOverlapping();
