<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserBalanceTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $adminUser;
    private UserModel $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77a',
            'username' => 'admin_balance_test',
            'name' => 'System Admin',
            'email' => 'admin_balance_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 100.0,
        ]);

        $this->normalUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77b',
            'username' => 'user_balance_test',
            'name' => 'Regular User',
            'email' => 'user_balance_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 20.0,
        ]);
    }

    public function test_admin_can_subtract_user_balance(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/v1/admin/users/{$this->normalUser->id}/adjust-balance", [
                'type' => 'subtract',
                'amount' => 7.25,
                'reason' => 'Manual correction',
            ]);

        $response->assertOk()
            ->assertJsonPath('user.balance', 12.75);

        $this->assertEquals(12.75, (float) $this->normalUser->fresh()->balance);
    }

    public function test_admin_cannot_subtract_more_than_user_balance(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/v1/admin/users/{$this->normalUser->id}/adjust-balance", [
                'type' => 'subtract',
                'amount' => 20.01,
            ]);

        $response->assertBadRequest();
        $this->assertEquals(20.0, (float) $this->normalUser->fresh()->balance);
    }

    private function adminHeaders(): array
    {
        $token = resolve(JwtService::class)->generateToken([
            'sub' => $this->adminUser->id,
            'email' => $this->adminUser->email,
            'role' => $this->adminUser->role,
        ], 1800);

        return ['Authorization' => "Bearer {$token}"];
    }
}
