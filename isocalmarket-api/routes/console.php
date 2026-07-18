<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
