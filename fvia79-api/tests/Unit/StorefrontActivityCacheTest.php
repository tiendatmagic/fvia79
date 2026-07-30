<?php

namespace Tests\Unit;

use App\Application\Services\StorefrontActivityCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StorefrontActivityCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_caches_each_feed_and_invalidates_related_keys(): void
    {
        $purchaseCalls = 0;
        $miniCalls = 0;
        $depositCalls = 0;

        $purchases = function () use (&$purchaseCalls): array {
            return ['call' => ++$purchaseCalls];
        };
        $mini = function () use (&$miniCalls): array {
            return ['call' => ++$miniCalls];
        };
        $deposits = function () use (&$depositCalls): array {
            return ['call' => ++$depositCalls];
        };

        StorefrontActivityCache::rememberRecentPurchases($purchases);
        StorefrontActivityCache::rememberRecentPurchases($purchases);
        StorefrontActivityCache::rememberMiniRecentPurchases($mini);
        StorefrontActivityCache::rememberMiniRecentPurchases($mini);
        StorefrontActivityCache::rememberRecentDeposits($deposits);
        StorefrontActivityCache::rememberRecentDeposits($deposits);

        $this->assertSame(1, $purchaseCalls);
        $this->assertSame(1, $miniCalls);
        $this->assertSame(1, $depositCalls);

        StorefrontActivityCache::invalidatePurchases();
        StorefrontActivityCache::rememberRecentPurchases($purchases);
        StorefrontActivityCache::rememberMiniRecentPurchases($mini);
        StorefrontActivityCache::rememberRecentDeposits($deposits);

        $this->assertSame(2, $purchaseCalls);
        $this->assertSame(2, $miniCalls);
        $this->assertSame(1, $depositCalls);

        StorefrontActivityCache::invalidateDeposits();
        StorefrontActivityCache::rememberRecentDeposits($deposits);

        $this->assertSame(2, $depositCalls);
    }
}
