<?php

namespace App\Application\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

final class StorefrontActivityCache
{
    public const RECENT_PURCHASES_KEY = 'storefront:recent-purchases:v1';

    public const MINI_RECENT_PURCHASES_KEY = 'storefront:mini-recent-purchases:v1';

    public const RECENT_DEPOSITS_KEY = 'storefront:recent-deposits:v1';

    private const TTL_SECONDS = 15;

    public static function rememberRecentPurchases(Closure $resolver): array
    {
        return Cache::remember(self::RECENT_PURCHASES_KEY, self::TTL_SECONDS, $resolver);
    }

    public static function rememberMiniRecentPurchases(Closure $resolver): array
    {
        return Cache::remember(self::MINI_RECENT_PURCHASES_KEY, self::TTL_SECONDS, $resolver);
    }

    public static function rememberRecentDeposits(Closure $resolver): array
    {
        return Cache::remember(self::RECENT_DEPOSITS_KEY, self::TTL_SECONDS, $resolver);
    }

    public static function invalidatePurchases(): void
    {
        Cache::forget(self::RECENT_PURCHASES_KEY);
        Cache::forget(self::MINI_RECENT_PURCHASES_KEY);
    }

    public static function invalidateDeposits(): void
    {
        Cache::forget(self::RECENT_DEPOSITS_KEY);
    }

    public static function clear(): void
    {
        self::invalidatePurchases();
        self::invalidateDeposits();
    }
}
