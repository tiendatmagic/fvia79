<?php

namespace App\Providers;

use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\CouponRepositoryInterface;
use App\Domain\Repositories\DepositRepositoryInterface;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\CategoryRepository;
use App\Infrastructure\Persistence\Repositories\CouponRepository;
use App\Infrastructure\Persistence\Repositories\DepositRepository;
use App\Infrastructure\Persistence\Repositories\InventoryBatchRepository;
use App\Infrastructure\Persistence\Repositories\InventoryItemRepository;
use App\Infrastructure\Persistence\Repositories\OrderRepository;
use App\Infrastructure\Persistence\Repositories\ProductRepository;
use App\Infrastructure\Persistence\Repositories\SystemSettingRepository;
use App\Infrastructure\Persistence\Repositories\UserRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );
        $this->app->bind(
            InventoryBatchRepositoryInterface::class,
            InventoryBatchRepository::class
        );
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
        $this->app->bind(
            DepositRepositoryInterface::class,
            DepositRepository::class
        );
        $this->app->bind(
            SystemSettingRepositoryInterface::class,
            SystemSettingRepository::class
        );
        $this->app->bind(
            CouponRepositoryInterface::class,
            CouponRepository::class
        );
        $this->app->bind(
            InventoryItemRepositoryInterface::class,
            InventoryItemRepository::class
        );
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(40)->by($request->ip());
        });
        RateLimiter::for('auth', function (Request $request) {
            $key = $request->ip().'|'.strtolower((string) $request->input('email', ''));

            return Limit::perMinute(10)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again in 1 minute.',
                ], 429);
            });
        });
        RateLimiter::for('database-backup-export', function (Request $request) {
            return Limit::perMinute(10)
                ->by((string) ($request->user()?->id ?? $request->ip()))
                ->response(fn () => response()->json([
                    'message' => __('messages.database_backup_rate_limited'),
                ], 429));
        });
        RateLimiter::for('database-backup-restore', function (Request $request) {
            return Limit::perMinutes(10, 3)
                ->by((string) ($request->user()?->id ?? $request->ip()))
                ->response(fn () => response()->json([
                    'message' => __('messages.database_restore_rate_limited'),
                ], 429));
        });
    }
}
