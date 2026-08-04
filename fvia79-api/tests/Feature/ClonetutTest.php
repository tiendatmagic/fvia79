<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use App\Integrations\Suppliers\ClonetutConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClonetutTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(string $username = 'admin_clonetut'): string
    {
        $admin = UserModel::create([
            'username' => $username,
            'name' => 'Admin Clonetut',
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

    public function test_templates_endpoint_returns_clonetut_when_enabled(): void
    {
        config()->set('services.clonetut.enabled', true);
        $token = $this->adminToken('admin_ct_1');

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/suppliers/templates');

        $response->assertOk()
            ->assertJsonFragment(['code' => 'clonetut', 'catalog_driver' => 'clonetut']);
    }

    public function test_templates_endpoint_hides_clonetut_when_disabled(): void
    {
        config()->set('services.clonetut.enabled', false);
        $token = $this->adminToken('admin_ct_2');

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/suppliers/templates');

        $response->assertOk()
            ->assertJsonMissing(['code' => 'clonetut']);
    }

    public function test_clonetut_fetch_account_and_balance(): void
    {
        Http::fake([
            'https://clonetut.net/api/profile.php*' => Http::response([
                'status' => 'success',
                'data' => [
                    'username' => 'testuser',
                    'balance' => 500000,
                ],
            ], 200),
        ]);

        $supplier = SupplierModel::create([
            'code' => 'clonetut_test',
            'name' => 'Clonetut Test',
            'base_url' => 'https://clonetut.net',
            'api_key' => 'test-api-key',
            'catalog_driver' => 'clonetut',
            'enabled' => true,
        ]);

        $connector = app(ClonetutConnector::class);
        $account = $connector->fetchAccount($supplier);
        $balance = $connector->fetchBalance($supplier);

        $this->assertSame(500000.0, $balance);
        $this->assertSame('testuser', $account['username']);
    }

    public function test_clonetut_fetch_catalog(): void
    {
        Http::fake([
            'https://clonetut.net/api/products.php*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'id' => '101',
                        'name' => 'Clone FB Nam',
                        'category_name' => 'Clone Facebook',
                        'price' => 5000,
                        'quantity' => 20,
                        'description' => 'Clone FB co 2FA',
                    ],
                ],
            ], 200),
        ]);

        $supplier = SupplierModel::create([
            'code' => 'clonetut_cat',
            'name' => 'Clonetut Catalog',
            'base_url' => 'https://clonetut.net',
            'api_key' => 'test-api-key',
            'catalog_driver' => 'clonetut',
            'enabled' => true,
        ]);

        $catalog = app(ClonetutConnector::class)->fetchCatalog($supplier);

        $this->assertCount(1, $catalog);
        $this->assertSame('Clone Facebook', $catalog[0]['name']);
        $this->assertSame('101', $catalog[0]['children'][0]['id']);
        $this->assertSame(5000.0, $catalog[0]['children'][0]['price']);
        $this->assertSame(20, $catalog[0]['children'][0]['quantity']);
    }

    public function test_clonetut_purchase_success(): void
    {
        Http::fake([
            'https://clonetut.net/api/buy_product*' => Http::response([
                'status' => 'success',
                'msg' => 'Tạo đơn hàng thành công!',
                'trans_id' => 'JF465f728224ce11',
                'data' => [
                    '1000040304952|SECRET1',
                    '1000087676467|SECRET2',
                ],
            ], 200),
        ]);

        $supplier = SupplierModel::create([
            'code' => 'clonetut_buy',
            'name' => 'Clonetut Buy',
            'base_url' => 'https://clonetut.net',
            'api_key' => 'test-api-key',
            'catalog_driver' => 'clonetut',
            'enabled' => true,
        ]);

        $order = app(ClonetutConnector::class)->purchase($supplier, '101', 2);

        $this->assertSame('JF465f728224ce11', $order['order_id']);
        $this->assertCount(2, $order['resources']);
        $this->assertSame('1000040304952|SECRET1', $order['resources'][0]);
    }
}
