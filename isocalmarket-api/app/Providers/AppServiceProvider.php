<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limiter chung cho toàn bộ API: 60 requests/phút
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(40)->by($request->ip());
        });

        // Rate limiter nghiêm ngặt cho auth (chống brute-force):
        // Tối đa 10 requests/phút theo IP + email kết hợp
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
