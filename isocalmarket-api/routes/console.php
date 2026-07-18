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

    // 1. Quét các hóa đơn đang chờ thanh toán (pending_payment)
    $pendingDeposits = DepositModel::where('status', 'pending_payment')->get();

    if ($pendingDeposits->isEmpty()) {
        $this->info('No pending deposits found.');
        return;
    }

    foreach ($pendingDeposits as $deposit) {
        try {
            $this->info("Checking deposit code: {$deposit->code} (ID: {$deposit->id})");

            // Đối soát giao dịch trên blockchain
            $matched = $depositService->checkTransaction($deposit->id, $deposit->user_id);

            if ($matched) {
                $this->info("Successfully matched and completed deposit code: {$deposit->code}");
            } else {
                // Kiểm tra xem hóa đơn đã quá 16 phút chưa (960 giây để tạo dung sai trễ mạng)
                $createdTime = $deposit->created_at->getTimestamp();
                $now = time();
                if (($now - $createdTime) > 960) {
                    $deposit->update(['status' => 'expired']);
                    $this->info("Deposit code {$deposit->code} has expired (exceeded 15 minutes window + 1 minute grace period).");
                } else {
                    $this->info("Deposit code {$deposit->code} is still pending within the payment window.");
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to check pending deposit ID {$deposit->id}: " . $e->getMessage());
            $this->error("Error checking deposit ID {$deposit->id}: " . $e->getMessage());
        }
    }

    $this->info('Pending deposits check completed.');
})->purpose('Scan blockchain and reconcile pending USDT BEP20 deposits');

// Đăng ký lịch chạy định kỳ mỗi phút
Schedule::command('deposit:check-pending')->everyMinute();

