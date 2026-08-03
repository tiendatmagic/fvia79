<?php

namespace Tests\Feature;

use App\Application\Services\SupplierCatalogSyncService;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use App\Integrations\Suppliers\Muabm365Connector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Muabm365Test extends TestCase
{
    use RefreshDatabase;

    private function adminToken(string $username = 'admin_test'): string
    {
        $admin = UserModel::create([
            'username' => $username,
            'name' => 'Admin Test',
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

    public function test_templates_endpoint_returns_muabm365_when_enabled(): void
    {
        config()->set('services.muabm365.enabled', true);
        $token = $this->adminToken('admin_templates_1');

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/suppliers/templates');

        $response->assertOk()
            ->assertJsonFragment(['code' => 'ads7979'])
            ->assertJsonFragment(['code' => 'muabm365', 'catalog_driver' => 'muabm365']);
    }

    public function test_templates_endpoint_hides_muabm365_when_disabled(): void
    {
        config()->set('services.muabm365.enabled', false);
        $token = $this->adminToken('admin_templates_2');

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/suppliers/templates');

        $response->assertOk()
            ->assertJsonFragment(['code' => 'ads7979'])
            ->assertJsonMissing(['code' => 'muabm365']);
    }

    public function test_can_fetch_muabm365_balance(): void
    {
        $token = $this->adminToken('admin_balance');

        Http::fake([
            'https://muabm365.top/api.php*' => Http::response([
                'code' => 0,
                'message' => 'success',
                'balance' => 150000,
            ]),
        ]);

        $supplier = SupplierModel::where('code', 'muabm365')->first() ?: SupplierModel::create([
            'code' => 'muabm365',
            'name' => 'MUABM365',
            'base_url' => 'https://muabm365.top',
            'catalog_driver' => 'muabm365',
            'api_key' => 'test-muabm365-key',
            'enabled' => true,
        ]);
        $supplier->update(['api_key' => 'test-muabm365-key']);

        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/suppliers/muabm365/balance');

        $response->assertOk()
            ->assertJsonPath('balance', 150000)
            ->assertJsonPath('currency', 'VND');
    }

    public function test_can_sync_muabm365_catalog(): void
    {
        $token = $this->adminToken('admin_sync');

        Http::fake([
            'https://api.coingecko.com/*' => Http::response(['tether' => ['vnd' => 26000]]),
            'https://muabm365.top/api.php*' => Http::response([
                'code' => 0,
                'message' => 'success',
                'data' => [
                    [
                        'service_id' => 1,
                        'service_name' => 'BM50 CỔ NĂM 2019-2022',
                        'price' => '30000',
                        'description' => 'Old BM50 year 2017-2022',
                        'quantity' => 50,
                    ],
                    [
                        'service_id' => 2,
                        'service_name' => 'BM50 Ngâm',
                        'price' => '10000',
                        'description' => 'BM50 soaked',
                        'quantity' => 12,
                    ],
                ],
            ]),
        ]);

        $supplier = SupplierModel::where('code', 'muabm365')->first() ?: SupplierModel::create([
            'code' => 'muabm365',
            'name' => 'MUABM365',
            'base_url' => 'https://muabm365.top',
            'catalog_driver' => 'muabm365',
            'api_key' => 'test-muabm365-key',
            'enabled' => true,
        ]);
        $supplier->update(['api_key' => 'test-muabm365-key']);

        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/suppliers/muabm365/sync');

        $response->assertOk()
            ->assertJsonPath('result.products', 2)
            ->assertJsonPath('result.categories', 1);

        $this->assertDatabaseHas('products', [
            'supplier_id' => $supplier->id,
            'external_id' => '1',
            'name' => 'BM50 CỔ NĂM 2019-2022',
            'supplier_price_vnd' => 30000.0,
            'supplier_stock' => 50,
        ]);
    }

    public function test_can_purchase_via_muabm365_connector(): void
    {
        Http::fake([
            'https://muabm365.top/api.php*' => Http::response([
                'code' => 200,
                'message' => 'Tạo Đơn Hàng Thành Công',
                'order_id' => 999,
                'data' => [
                    'account_line_1|pass1|key1',
                ],
            ]),
        ]);

        $supplier = SupplierModel::where('code', 'muabm365')->first() ?: SupplierModel::create([
            'code' => 'muabm365',
            'name' => 'MUABM365',
            'base_url' => 'https://muabm365.top',
            'catalog_driver' => 'muabm365',
            'api_key' => 'test-key',
            'enabled' => true,
        ]);
        $supplier->update(['api_key' => 'test-key']);

        $connector = app(Muabm365Connector::class);
        $result = $connector->purchase($supplier, '1', 1);

        $this->assertSame('999', $result['order_id']);
        $this->assertEquals(['account_line_1|pass1|key1'], $result['resources']);
    }

    public function test_can_update_muabm365_supplier_without_api_key(): void
    {
        $token = $this->adminToken('admin_update_no_key');

        $supplier = SupplierModel::where('code', 'muabm365')->first() ?: SupplierModel::create([
            'code' => 'muabm365',
            'name' => 'MUABM365',
            'base_url' => 'https://muabm365.top',
            'catalog_driver' => 'muabm365',
            'api_key' => null,
            'enabled' => true,
        ]);
        $supplier->update(['api_key' => null]);

        $response = $this->withToken($token)
            ->putJson('/api/v1/admin/suppliers/muabm365', [
                'name' => 'MUABM365 Updated',
                'base_url' => 'https://muabm365.top',
                'price_adjustment_type' => 'fixed',
                'price_adjustment_value' => 500,
            ]);

        $response->assertOk()
            ->assertJsonPath('name', 'MUABM365 Updated');
    }
}
