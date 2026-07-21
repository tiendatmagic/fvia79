<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Domain\Repositories\UserRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Domain\Repositories\ProductRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\ProductRepository::class
        );
        $this->app->bind(
            \App\Domain\Repositories\CategoryRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\CategoryRepository::class
        );
        $this->app->bind(
            \App\Domain\Repositories\InventoryBatchRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\InventoryBatchRepository::class
        );
        $this->app->bind(
            \App\Domain\Repositories\OrderRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\OrderRepository::class
        );
        $this->app->bind(
            \App\Domain\Repositories\DepositRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\DepositRepository::class
        );
        $this->app->bind(
            \App\Domain\Repositories\SystemSettingRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\SystemSettingRepository::class
        );
        $this->app->bind(
            \App\Domain\Repositories\CouponRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\CouponRepository::class
        );
    }
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(40)->by($request->ip());
        });
        RateLimiter::for('auth', function (Request $request) {
            $key = $request->ip() . '|' . strtolower((string) $request->input('email', ''));
            return Limit::perMinute(10)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again in 1 minute.'
                ], 429);
            });
        });
    }
}
