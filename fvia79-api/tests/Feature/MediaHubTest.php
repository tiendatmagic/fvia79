<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use App\Integrations\Suppliers\MediaHubConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediaHubTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(string $username = 'admin_mediahub'): string
    {
        $admin = UserModel::create([
            'username' => $username,
            'name' => 'Admin MediaHub',
            'email' => "{$username}@example.com",
            'password' => 'password123',
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
            'status' => 'active',
        ]);

        return app(JwtService::class)->generateToken([
            'sub' => $admin->id,
            'email' => $admin->email,
            'role' => 'admin',
            'ver' => 0,
        ], 1800);
    }

    public function test_templates_endpoint_returns_mediahub_when_enabled(): void
    {
        config()->set('services.mediahub.enabled', true);
        $token = $this->adminToken('admin_mh_1');

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/suppliers/templates');

        $response->assertOk()
            ->assertJsonFragment(['code' => 'mediahub', 'catalog_driver' => 'mediahub']);
    }

    public function test_templates_endpoint_hides_mediahub_when_disabled(): void
    {
        config()->set('services.mediahub.enabled', false);
        $token = $this->adminToken('admin_mh_2');

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/suppliers/templates');

        $response->assertOk()
            ->assertJsonMissing(['code' => 'mediahub']);
    }

    public function test_mediahub_fetch_account_and_balance(): void
    {
        Http::fake([
            'https://canboso.com/api/v2/telegram-buyer/balance*' => Http::response([
                'success' => true,
                'balance' => 250000,
                'balanceVnd' => 250000,
                'requester' => [
                    'chatId' => 12345678,
                    'name' => 'testbuyer',
                ],
            ], 200),
        ]);

        $supplier = SupplierModel::create([
            'code' => 'mediahub_test',
            'name' => 'MediaHub Test',
            'base_url' => 'https://canboso.com',
            'api_key' => 'tgb_test_key',
            'catalog_driver' => 'mediahub',
            'enabled' => true,
        ]);

        $connector = app(MediaHubConnector::class);
        $account = $connector->fetchAccount($supplier);
        $balance = $connector->fetchBalance($supplier);

        $this->assertSame(250000.0, $balance);
        $this->assertSame('testbuyer', $account['username']);
        $this->assertSame('12345678', $account['id']);
    }

    public function test_mediahub_fetch_catalog(): void
    {
        Http::fake([
            'https://canboso.com/api/v2/telegram-buyer/products*' => Http::response([
                'success' => true,
                'products' => [
                    [
                        '_id' => '64f0c0f2b90c2b4c5a123456',
                        'product_name' => 'ChatGPT Plus 1 Tháng',
                        'categoryName' => 'CHATGPT / OPENAI',
                        'priceVnd' => 50000,
                        'availableQuantity' => 15,
                        'description' => 'Tài khoản ChatGPT Plus chính chủ',
                    ],
                ],
            ], 200),
        ]);

        $supplier = SupplierModel::create([
            'code' => 'mediahub_cat',
            'name' => 'MediaHub Catalog',
            'base_url' => 'https://canboso.com',
            'api_key' => 'tgb_test_key',
            'catalog_driver' => 'mediahub',
            'enabled' => true,
        ]);

        $catalog = app(MediaHubConnector::class)->fetchCatalog($supplier);

        $this->assertCount(1, $catalog);
        $this->assertSame('CHATGPT / OPENAI', $catalog[0]['name']);
        $this->assertSame('64f0c0f2b90c2b4c5a123456', $catalog[0]['children'][0]['id']);
        $this->assertSame(50000.0, $catalog[0]['children'][0]['price']);
        $this->assertSame(15, $catalog[0]['children'][0]['quantity']);
    }

    public function test_mediahub_purchase_success(): void
    {
        Http::fake([
            'https://canboso.com/api/v2/telegram-buyer/purchase*' => Http::response([
                'success' => true,
                'orderCode' => 'ORDER123456',
                'amount' => 50000,
                'deliveredAccounts' => [
                    [
                        'user' => 'gptuser@example.com',
                        'password' => 'pass123',
                        'verifyEmail' => 'rec@example.com',
                    ],
                ],
            ], 200),
        ]);

        $supplier = SupplierModel::create([
            'code' => 'mediahub_buy',
            'name' => 'MediaHub Buy',
            'base_url' => 'https://canboso.com',
            'api_key' => 'tgb_test_key',
            'catalog_driver' => 'mediahub',
            'enabled' => true,
        ]);

        $order = app(MediaHubConnector::class)->purchase($supplier, '64f0c0f2b90c2b4c5a123456', 1);

        $this->assertSame('ORDER123456', $order['order_id']);
        $this->assertCount(1, $order['resources']);
        $this->assertSame('gptuser@example.com|pass123|rec@example.com', $order['resources'][0]);
    }
}
