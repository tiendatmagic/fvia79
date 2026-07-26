<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\CouponModel;
use App\Infrastructure\Persistence\Repositories\CouponRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CouponStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_filter_uses_the_configured_local_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-23 05:30:00', 'UTC'));

        CouponModel::create([
            'code' => 'ACTIVE_LOCAL_TIME',
            'type' => 'percentage',
            'value' => 10,
            'min_spend' => 0,
            'start_date' => '2026-07-23 12:00:00',
            'end_date' => '2026-07-23 13:00:00',
            'usage_limit' => 1,
            'used_count' => 0,
        ]);

        $result = app(CouponRepository::class)->findAllPaginated(
            page: 1,
            limit: 10,
            status: 'active',
        );

        $this->assertSame('Asia/Ho_Chi_Minh', config('app.timezone'));
        $this->assertSame(1, $result['total']);
        $this->assertSame('ACTIVE_LOCAL_TIME', $result['items'][0]->getCode());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
