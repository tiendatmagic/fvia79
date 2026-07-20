<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Infrastructure\Security\JwtService;
use App\Application\Services\DepositService;

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
            'amount_usdt' => 10.25,
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
            'balance' => 10.25
        ]);
    }

    public function test_automatic_bep20_reconciliation_credits_usdt_not_vnd(): void
    {
        $wallet = '0x1111111111111111111111111111111111111111';
        SystemSettingModel::create(['key' => 'admin_wallet_address', 'value' => $wallet]);
        SystemSettingModel::create(['key' => 'scanner_api_key', 'value' => 'test-scanner-key']);
        Cache::flush();

        $deposit = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77d',
            'user_id' => $this->normalUser->id,
            'code' => 'AUTO01',
            'amount_usdt' => 10.5,
            'amount_to_pay' => 10.5,
            'amount_vnd' => 275898.0,
            'status' => 'pending_payment',
            'payment_method' => 'bep20',
            'admin_wallet_address' => $wallet,
        ]);

        Http::fake([
            'deep-index.moralis.io/*' => Http::response([
                'result' => [[
                    'address' => '0x55d398326f99059ff775485246999027b3197955',
                    'to_address' => $wallet,
                    'value' => '10500000000000000000',
                    'transaction_hash' => '0xautomaticdeposit',
                    'block_timestamp' => now()->toIso8601String(),
                ]],
            ], 200),
        ]);

        $matched = resolve(DepositService::class)->checkTransaction(
            $deposit->id,
            $this->normalUser->id
        );

        $this->assertTrue($matched);
        $this->assertDatabaseHas('deposits', [
            'id' => $deposit->id,
            'status' => 'completed',
            'tx_hash' => '0xautomaticdeposit',
        ]);
        $this->assertEquals(10.5, (float) $this->normalUser->fresh()->balance);
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
