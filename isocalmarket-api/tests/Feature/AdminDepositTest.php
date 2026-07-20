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
            'amount_to_pay' => 10.2501,
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

        $duplicateResponse = $this->withHeaders($this->getHeadersForUser($this->adminUser))
            ->postJson("/api/v1/admin/deposits/{$deposit->id}/approve", [
                'tx_hash' => '0xanotherhash'
            ]);

        $duplicateResponse->assertStatus(400);
        $this->assertEquals(10.25, (float) $this->normalUser->fresh()->balance);
    }

    public function test_manual_approval_requires_transaction_hash(): void
    {
        $deposit = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a780',
            'user_id' => $this->normalUser->id,
            'code' => 'NOHASH',
            'amount_usdt' => 10.0,
            'amount_to_pay' => 10.0001,
            'amount_vnd' => 262762.0,
            'status' => 'pending_payment',
            'payment_method' => 'bep20',
        ]);

        $response = $this->withHeaders($this->getHeadersForUser($this->adminUser))
            ->postJson("/api/v1/admin/deposits/{$deposit->id}/approve", []);

        $response->assertStatus(422);
        $this->assertDatabaseHas('deposits', ['id' => $deposit->id, 'status' => 'pending_payment']);
        $this->assertEquals(0.0, (float) $this->normalUser->fresh()->balance);
    }

    public function test_rejected_deposit_cannot_be_approved(): void
    {
        $deposit = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a781',
            'user_id' => $this->normalUser->id,
            'code' => 'FAILED',
            'amount_usdt' => 10.0,
            'amount_to_pay' => 10.0001,
            'amount_vnd' => 262762.0,
            'status' => 'failed',
            'payment_method' => 'bep20',
        ]);

        $response = $this->withHeaders($this->getHeadersForUser($this->adminUser))
            ->postJson("/api/v1/admin/deposits/{$deposit->id}/approve", [
                'tx_hash' => '0xfaileddeposit',
            ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('deposits', ['id' => $deposit->id, 'status' => 'failed']);
        $this->assertEquals(0.0, (float) $this->normalUser->fresh()->balance);
    }

    public function test_automatic_bep20_reconciliation_credits_usdt_not_vnd(): void
    {
        $wallet = '0x1111111111111111111111111111111111111111';
        SystemSettingModel::updateOrCreate(['key' => 'admin_wallet_address'], ['value' => $wallet]);
        SystemSettingModel::updateOrCreate(['key' => 'scanner_api_key'], ['value' => 'test-scanner-key']);
        Cache::flush();

        $deposit = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77d',
            'user_id' => $this->normalUser->id,
            'code' => 'AUTO01',
            'amount_usdt' => 10.0,
            'amount_to_pay' => 10.0001,
            'amount_vnd' => 262762.0,
            'status' => 'pending_payment',
            'payment_method' => 'bep20',
            'admin_wallet_address' => $wallet,
        ]);

        Http::fake([
            'deep-index.moralis.io/*' => Http::response([
                'result' => [[
                    'address' => '0x55d398326f99059ff775485246999027b3197955',
                    'to_address' => $wallet,
                    'value' => '10000100000000000000',
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
        $this->assertEquals(10.0, (float) $this->normalUser->fresh()->balance);
    }

    public function test_automatic_binance_reconciliation_credits_usdt_not_vnd(): void
    {
        SystemSettingModel::updateOrCreate(['key' => 'binance_api_key'], ['value' => 'test-binance-key']);
        SystemSettingModel::updateOrCreate(['key' => 'binance_api_secret'], ['value' => 'test-binance-secret']);
        Cache::flush();

        $deposit = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77e',
            'user_id' => $this->normalUser->id,
            'code' => 'BINA01',
            'amount_usdt' => 7.5,
            'amount_to_pay' => 7.5001,
            'amount_vnd' => 197070.0,
            'status' => 'pending_payment',
            'payment_method' => 'binance',
        ]);

        Http::fake([
            'api.binance.com/*' => Http::response([
                'code' => '000000',
                'message' => 'success',
                'success' => true,
                'data' => [[
                    'currency' => 'USDT',
                    'amount' => '7.5001',
                    'transactionId' => 'binance-transaction-001',
                    'transactionTime' => now()->getTimestampMs(),
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
            'tx_hash' => 'binance-transaction-001',
        ]);
        $this->assertEquals(7.5, (float) $this->normalUser->fresh()->balance);
    }

    public function test_batch_reconciliation_keeps_bep20_and_binance_caches_separate(): void
    {
        $wallet = '0x1111111111111111111111111111111111111111';
        SystemSettingModel::updateOrCreate(['key' => 'admin_wallet_address'], ['value' => $wallet]);
        SystemSettingModel::updateOrCreate(['key' => 'scanner_api_key'], ['value' => 'test-scanner-key']);
        SystemSettingModel::updateOrCreate(['key' => 'binance_api_key'], ['value' => 'test-binance-key']);
        SystemSettingModel::updateOrCreate(['key' => 'binance_api_secret'], ['value' => 'test-binance-secret']);
        Cache::flush();

        $bep20 = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a782',
            'user_id' => $this->normalUser->id,
            'code' => 'MIXBEP',
            'amount_usdt' => 1.0,
            'amount_to_pay' => 1.0001,
            'amount_vnd' => 26276.0,
            'status' => 'pending_payment',
            'payment_method' => 'bep20',
            'admin_wallet_address' => $wallet,
        ]);
        $binance = DepositModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a783',
            'user_id' => $this->normalUser->id,
            'code' => 'MIXBIN',
            'amount_usdt' => 2.0,
            'amount_to_pay' => 2.0001,
            'amount_vnd' => 52552.0,
            'status' => 'pending_payment',
            'payment_method' => 'binance',
        ]);

        Http::fake([
            'deep-index.moralis.io/*' => Http::response([
                'result' => [[
                    'address' => '0x55d398326f99059ff775485246999027b3197955',
                    'to_address' => $wallet,
                    'value' => '1000100000000000000',
                    'transaction_hash' => '0xmixedbep20',
                    'block_timestamp' => now()->toIso8601String(),
                ]],
            ], 200),
            'api.binance.com/*' => Http::response([
                'code' => '000000',
                'message' => 'success',
                'success' => true,
                'data' => [[
                    'currency' => 'USDT',
                    'amount' => '2.0001',
                    'transactionId' => 'mixed-binance-transaction',
                    'transactionTime' => now()->getTimestampMs(),
                ]],
            ], 200),
        ]);

        resolve(DepositService::class)->checkAllPendingDeposits();

        $this->assertEquals('completed', $bep20->fresh()->status);
        $this->assertEquals('completed', $binance->fresh()->status);
        $this->assertEquals(3.0, (float) $this->normalUser->fresh()->balance);
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
