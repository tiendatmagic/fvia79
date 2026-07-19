<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Security\JwtService;

class AdminDepositTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $adminUser;
    private UserModel $normalUser;
    private JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtService = resolve(JwtService::class);

        $this->adminUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77a',
            'username' => 'admin_test',
            'name' => 'System Admin',
            'email' => 'admin_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 100000.0,
            'api_key' => 'ADMIN-KEY-TEST-12345'
        ]);

        $this->normalUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77b',
            'username' => 'user_test',
            'name' => 'Regular User',
            'email' => 'user_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 0.0,
            'api_key' => 'USER-KEY-TEST-12345'
        ]);
    }

    private function getHeadersForUser(UserModel $user): array
    {
        $token = $this->jwtService->generateToken([
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ], 1800);

        return [
            'Authorization' => "Bearer {$token}"
        ];
    }

    public function test_admin_can_fetch_all_deposits(): void
    {
        DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77c',
            'user_id' => $this->normalUser->id,
            'code' => 'DEP001',
            'amount_usdt' => 100.0,
            'amount_to_pay' => 100.0123,
            'amount_vnd' => 2500000.0,
            'status' => 'pending_payment',
            'payment_method' => 'bep20'
        ]);

        $response = $this->withHeaders($this->getHeadersForUser($this->adminUser))
            ->getJson('/api/v1/admin/deposits');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.code', 'DEP001');
    }

    public function test_normal_user_is_forbidden_to_fetch_deposits(): void
    {
        $response = $this->withHeaders($this->getHeadersForUser($this->normalUser))
            ->getJson('/api/v1/admin/deposits');

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_deposit_manually(): void
    {
        $deposit = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77c',
            'user_id' => $this->normalUser->id,
            'code' => 'DEP001',
            'amount_usdt' => 100.0,
            'amount_to_pay' => 100.0123,
            'amount_vnd' => 2500000.0,
            'status' => 'pending_payment',
            'payment_method' => 'bep20'
        ]);

        $response = $this->withHeaders($this->getHeadersForUser($this->adminUser))
            ->postJson("/api/v1/admin/deposits/{$deposit->id}/approve", [
                'tx_hash' => '0xmockedtxhash123'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('deposits', [
            'id' => $deposit->id,
            'status' => 'completed',
            'tx_hash' => '0xmockedtxhash123'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->normalUser->id,
            'balance' => 2500000.0
        ]);
    }

    public function test_admin_can_reject_deposit_manually(): void
    {
        $deposit = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77c',
            'user_id' => $this->normalUser->id,
            'code' => 'DEP001',
            'amount_usdt' => 100.0,
            'amount_to_pay' => 100.0123,
            'amount_vnd' => 2500000.0,
            'status' => 'pending_payment',
            'payment_method' => 'bep20'
        ]);

        $response = $this->withHeaders($this->getHeadersForUser($this->adminUser))
            ->postJson("/api/v1/admin/deposits/{$deposit->id}/reject");

        $response->assertStatus(200);

        $this->assertDatabaseHas('deposits', [
            'id' => $deposit->id,
            'status' => 'failed'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->normalUser->id,
            'balance' => 0.0
        ]);
    }
}
