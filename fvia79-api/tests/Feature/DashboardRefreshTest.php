<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_flag_forces_dashboard_stats_cache_rebuild(): void
    {
        Cache::flush();

        $admin = UserModel::create([
            'username' => 'dashboard_refresh_admin',
            'name' => 'Dashboard Refresh Admin',
            'email' => 'dashboard-refresh@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'balance' => 0,
            'status' => 'active',
        ]);
        $token = app(JwtService::class)->generateToken([
            'sub' => $admin->id,
            'email' => $admin->email,
            'role' => $admin->role,
            'ver' => 0,
        ], 1800);

        $cacheKey = 'admin_dashboard_stats_'.md5('____5_1_10');
        Cache::put($cacheKey, ['total_orders' => 999999], 60);

        $headers = ['Authorization' => "Bearer {$token}"];
        $this->withHeaders($headers)
            ->getJson('/api/v1/admin/dashboard-stats')
            ->assertOk()
            ->assertJsonPath('total_orders', 999999);

        $this->withHeaders($headers)
            ->getJson('/api/v1/admin/dashboard-stats?refresh=1')
            ->assertOk()
            ->assertJsonPath('total_orders', 0);

        $this->assertNotSame(999999, Cache::get($cacheKey)['total_orders'] ?? null);
    }
}
