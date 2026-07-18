<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Application\Services\DepositService;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('auth:force-logout-all', function () {
    $this->info('Starting force logout of all users...');

    $affected = \App\Infrastructure\Persistence\Eloquent\UserModel::query()->update([
        'refresh_token_hash' => null,
        'refresh_token_expired_at' => null
    ]);
    $this->comment("Cleared refresh tokens for {$affected} users in database.");

    // 2. Sinh APP_KEY mới để vô hiệu hóa toàn bộ Access Token (JWT) hiện có
    $this->comment('Regenerating APP_KEY to invalidate all existing access tokens (JWT)...');

    // Gọi lệnh Artisan 'key:generate'
    Artisan::call('key:generate', ['--force' => true]);

    // Đọc log output của key:generate
    $output = Artisan::output();
    $this->info(trim($output));

    $this->info('Successfully logged out all users and invalidated all active tokens!');
})->purpose('Force logout all users by clearing all active database refresh tokens and regenerating APP_KEY');

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

// Đăng ký lịch chạy định kỳ mỗi phút
Schedule::command('deposit:check-pending')->everyMinute();

