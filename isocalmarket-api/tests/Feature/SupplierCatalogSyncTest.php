<?php

namespace Tests\Feature;

use App\Application\Services\SupplierCatalogSyncService;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use App\Integrations\Suppliers\CloneStoreConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupplierCatalogSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_and_updates_ads7979_catalog_without_duplicates(): void
    {
        $this->fakeSupplierAndRateApis();
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update([
            'price_adjustment_type' => 'fixed',
            'price_adjustment_value' => 20000,
        ]);
        $service = app(SupplierCatalogSyncService::class);
        $first = $service->sync($supplier);
        $second = $service->sync($supplier->fresh());

        $this->assertSame(['categories' => 1, 'products' => 1], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, CategoryModel::where('supplier_id', $supplier->id)->count());
        $this->assertSame(1, ProductModel::where('supplier_id', $supplier->id)->count());
        $product = ProductModel::where('external_id', '2')->firstOrFail();
        $this->assertSame(20000.0, $product->supplier_price_vnd);
        $this->assertSame(1.53846154, $product->price);
        $this->assertSame(17, $product->supplier_stock);
        $this->assertSame([], $product->attributes);
        $this->assertSame('success', $supplier->fresh()->last_sync_status);
        Http::assertSentCount(3);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.coingecko.com/api/v3/simple/price'));
    }

    public function test_percent_adjustment_is_converted_to_usd(): void
    {
        $this->fakeSupplierAndRateApis();
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update([
            'price_adjustment_type' => 'percent',
            'price_adjustment_value' => 25,
        ]);
        app(SupplierCatalogSyncService::class)->sync($supplier);

        $this->assertSame(0.96153846, ProductModel::where('external_id', '2')->value('price'));
    }

    public function test_sync_keeps_new_products_inactive_when_supplier_products_are_disabled(): void
    {
        $this->fakeSupplierAndRateApis();
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update(['products_enabled' => false]);

        app(SupplierCatalogSyncService::class)->sync($supplier->fresh());

        $this->assertSame(
            'inactive',
            ProductModel::where('supplier_id', $supplier->id)->where('external_id', '2')->value('status'),
        );
    }

    public function test_admin_can_activate_and_deactivate_all_supplier_products(): void
    {
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        ProductModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'bulk-status-product',
            'name' => 'Bulk status product',
            'slug' => 'bulk-status-product',
            'price' => 1,
            'status' => 'active',
            'attributes' => [],
        ]);
        ProductModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'manually-disabled-product',
            'name' => 'Manually disabled product',
            'slug' => 'manually-disabled-product',
            'price' => 1,
            'status' => 'inactive',
            'attributes' => [],
        ]);
        $token = $this->adminToken('supplier_bulk_status');

        $this->withToken($token)
            ->putJson('/api/v1/admin/suppliers/ads7979', ['products_enabled' => false])
            ->assertOk()
            ->assertJsonPath('products_enabled', false);
        $this->assertSame(
            2,
            ProductModel::where('supplier_id', $supplier->id)->where('status', 'inactive')->count(),
        );

        $this->withToken($token)
            ->putJson('/api/v1/admin/suppliers/ads7979', ['products_enabled' => true])
            ->assertOk()
            ->assertJsonPath('products_enabled', true);
        $this->assertSame(
            'active',
            ProductModel::where('external_id', 'bulk-status-product')->value('status'),
        );
        $this->assertSame(
            'inactive',
            ProductModel::where('external_id', 'manually-disabled-product')->value('status'),
        );
    }

    public function test_supplier_stock_is_returned_as_available_count(): void
    {
        $this->fakeSupplierAndRateApis();
        app(SupplierCatalogSyncService::class)->syncByCode('ads7979');

        $response = $this->getJson('/api/v1/products?status=active');

        $response->assertOk()
            ->assertJsonPath('0.available_count', 17)
            ->assertJsonPath('0.supplier.code', 'ads7979')
            ->assertJsonPath('0.supplier.external_id', '2');
    }

    public function test_low_vnd_price_keeps_enough_usd_precision_for_vnd_display(): void
    {
        Cache::forget('usdt_to_vnd_rate');
        config()->set('services.ads7979.api_key', 'test-api-key');
        $catalog = $this->catalog();
        $catalog[0]['children'][0]['price'] = 270;
        Http::fake([
            'https://ads7979.com/api/v2/categories' => Http::response($catalog),
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26300],
            ]),
        ]);

        app(SupplierCatalogSyncService::class)->syncByCode('ads7979');

        $priceUsd = ProductModel::where('external_id', '2')->value('price');
        $this->assertSame(0.01026616, $priceUsd);
        $this->assertSame(270, (int) round($priceUsd * 26300));
    }

    public function test_out_of_stock_import_can_be_disabled_and_products_return_when_restocked(): void
    {
        Cache::forget('usdt_to_vnd_rate');
        config()->set('services.ads7979.api_key', 'test-api-key');
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $this->withToken($this->adminToken('supplier_stock_filter_admin'))
            ->putJson('/api/v1/admin/suppliers/ads7979', ['import_out_of_stock' => false])
            ->assertOk()
            ->assertJsonPath('import_out_of_stock', false);

        $outOfStockCatalog = $this->catalog();
        $outOfStockCatalog[0]['children'][0]['quantity'] = 0;
        $catalogSequence = Http::sequence()
            ->push($outOfStockCatalog)
            ->push($this->catalog())
            ->push($outOfStockCatalog)
            ->push($this->catalog());
        Http::fake([
            'https://ads7979.com/api/v2/categories' => $catalogSequence,
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26000],
            ]),
        ]);

        $result = app(SupplierCatalogSyncService::class)->sync($supplier->fresh());
        $this->assertSame(0, $result['products']);
        $this->assertDatabaseMissing('products', [
            'supplier_id' => $supplier->id,
            'external_id' => '2',
        ]);

        app(SupplierCatalogSyncService::class)->sync($supplier->fresh());
        $this->assertDatabaseHas('products', [
            'supplier_id' => $supplier->id,
            'external_id' => '2',
            'supplier_stock' => 17,
            'status' => 'active',
        ]);

        app(SupplierCatalogSyncService::class)->sync($supplier->fresh());
        $this->assertDatabaseHas('products', [
            'supplier_id' => $supplier->id,
            'external_id' => '2',
            'supplier_stock' => 0,
            'supplier_out_of_stock_hidden' => true,
            'status' => 'inactive',
        ]);

        app(SupplierCatalogSyncService::class)->sync($supplier->fresh());
        $this->assertDatabaseHas('products', [
            'supplier_id' => $supplier->id,
            'external_id' => '2',
            'supplier_stock' => 17,
            'supplier_out_of_stock_hidden' => false,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_add_and_sync_another_compatible_v2_supplier_without_new_connector(): void
    {
        Http::fake([
            'https://vlclone.com/api/v2/categories' => Http::response($this->catalog()),
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26000],
            ]),
        ]);
        $admin = UserModel::create([
            'username' => 'supplier_admin',
            'name' => 'Supplier Admin',
            'email' => 'supplier-admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
            'status' => 'active',
        ]);
        $token = app(JwtService::class)->generateToken([
            'sub' => $admin->id,
            'email' => $admin->email,
            'role' => 'admin',
            'ver' => 0,
        ], 1800);

        $create = $this->withToken($token)->postJson('/api/v1/admin/suppliers', [
            'name' => 'VLClone',
            'base_url' => 'https://vlclone.com',
            'api_key' => 'vlclone-test-key',
            'price_adjustment_type' => 'fixed',
            'price_adjustment_value' => 20000,
        ]);

        $create->assertCreated()
            ->assertJsonPath('name', 'VLClone')
            ->assertJsonPath('catalog_driver', 'clone_store_v2')
            ->assertJsonPath('has_api_key', true);
        $code = $create->json('code');
        $supplier = SupplierModel::where('code', $code)->firstOrFail();
        $this->assertNotSame(
            'vlclone-test-key',
            DB::table('suppliers')->where('id', $supplier->id)->value('api_key'),
        );

        $this->withToken($token)
            ->postJson("/api/v1/admin/suppliers/{$code}/sync")
            ->assertOk()
            ->assertJsonPath('result.products', 1);
        $this->assertSame(1, ProductModel::where('supplier_id', $supplier->id)->count());
    }

    public function test_compatible_supplier_driver_falls_back_to_v1_and_normalizes_its_catalog(): void
    {
        Http::fake([
            'https://vlclone.com/api/v2/categories' => Http::response(['message' => 'Unauthorized'], 401),
            'https://vlclone.com/api/v1/balance' => Http::response(['status' => true, 'balance' => 100000]),
            'https://vlclone.com/api/v1/categories' => Http::response([
                'data' => [[
                    'id' => 5,
                    'name' => 'V1 Category',
                    'icon' => null,
                    'list_product' => [[
                        'id_product' => 99,
                        'name' => 'V1 Product',
                        'desc' => 'V1 description',
                        'price' => 15000,
                        'quantity' => 7,
                        'hdsd' => 'Instructions',
                    ]],
                ]],
            ]),
        ]);
        $supplier = new SupplierModel([
            'code' => 'vlclone',
            'name' => 'VLClone',
            'base_url' => 'https://vlclone.com',
            'api_key' => 'v1-test-key',
            'catalog_driver' => CloneStoreConnector::V2,
        ]);
        $connector = app(CloneStoreConnector::class);

        $this->assertSame(CloneStoreConnector::V1, $connector->detectDriver($supplier));
        $supplier->catalog_driver = CloneStoreConnector::V1;
        $catalog = $connector->fetchCatalog($supplier);

        $this->assertSame(99, $catalog[0]['children'][0]['id']);
        $this->assertSame('V1 description', $catalog[0]['children'][0]['data']['description']);
        $this->assertSame(7, $catalog[0]['children'][0]['quantity']);
    }

    public function test_it_reads_balance_from_compatible_v2_supplier_account(): void
    {
        Http::fake([
            'https://ads7979.com/api/v2/me' => Http::response([
                'data' => [
                    'id' => 43,
                    'username' => 'supplier-user',
                    'email' => 'supplier@example.com',
                    'level' => 7,
                    'percent' => 10,
                    'balance' => 1250000.75,
                    'api_key' => 'must-not-be-forwarded',
                ],
            ]),
        ]);
        $supplier = new SupplierModel([
            'code' => 'ads7979',
            'name' => 'ADS7979',
            'base_url' => 'https://ads7979.com',
            'api_key' => 'v2-test-key',
            'catalog_driver' => CloneStoreConnector::V2,
        ]);

        $account = app(CloneStoreConnector::class)->fetchAccount($supplier);

        $this->assertSame(1250000.75, $account['balance']);
        $this->assertSame('supplier-user', $account['username']);
        $this->assertSame(7, $account['level']);
        $this->assertSame(10.0, $account['percent']);
        $this->assertArrayNotHasKey('api_key', $account);
        Http::assertSent(fn ($request) => $request->url() === 'https://ads7979.com/api/v2/me'
            && $request->hasHeader('Authorization', 'Bearer v2-test-key'));
    }

    public function test_it_reads_balance_from_compatible_v1_supplier_account(): void
    {
        Http::fake([
            'https://vlclone.com/api/v1/balance' => Http::response([
                'status' => true,
                'balance' => 500000,
            ]),
        ]);
        $supplier = new SupplierModel([
            'code' => 'vlclone',
            'name' => 'VLClone',
            'base_url' => 'https://vlclone.com',
            'api_key' => 'v1-test-key',
            'catalog_driver' => CloneStoreConnector::V1,
        ]);

        $balance = app(CloneStoreConnector::class)->fetchBalance($supplier);

        $this->assertSame(500000.0, $balance);
        Http::assertSent(fn ($request) => $request->url() === 'https://vlclone.com/api/v1/balance'
            && $request['api_key'] === 'v1-test-key');
    }

    public function test_it_purchases_and_normalizes_a_v1_supplier_order(): void
    {
        Http::fake([
            'https://vlclone.com/api/v1/buy' => Http::response([
                'status' => true,
                'order_id' => 'V1-ORDER-1',
                'data' => ['account-1|password-1', 'account-2|password-2'],
            ]),
        ]);
        $supplier = new SupplierModel([
            'code' => 'vlclone',
            'name' => 'VLClone',
            'base_url' => 'https://vlclone.com',
            'api_key' => 'v1-test-key',
            'catalog_driver' => CloneStoreConnector::V1,
        ]);

        $result = app(CloneStoreConnector::class)->purchase($supplier, '99', 2);

        $this->assertSame('V1-ORDER-1', $result['order_id']);
        $this->assertSame(['account-1|password-1', 'account-2|password-2'], $result['resources']);
        Http::assertSent(fn ($request) => $request->url() === 'https://vlclone.com/api/v1/buy'
            && $request['api_key'] === 'v1-test-key'
            && $request['id_product'] === '99'
            && $request['quantity'] === 2);
    }

    public function test_supplier_account_endpoint_requires_post_and_returns_allowlisted_data(): void
    {
        Http::fake([
            'https://ads7979.com/api/v2/me' => Http::response([
                'id' => 43,
                'username' => 'supplier-user',
                'email' => 'supplier@example.com',
                'level' => 7,
                'percent' => 10,
                'balance' => 1250000,
                'api_key' => 'must-not-be-forwarded',
            ]),
        ]);
        SupplierModel::where('code', 'ads7979')->firstOrFail()->update([
            'api_key' => 'v2-test-key',
            'catalog_driver' => CloneStoreConnector::V2,
        ]);
        $token = $this->adminToken('supplier_account_admin');

        $this->withToken($token)
            ->getJson('/api/v1/admin/suppliers/ads7979/balance')
            ->assertStatus(405);

        $this->withToken($token)
            ->postJson('/api/v1/admin/suppliers/ads7979/balance')
            ->assertOk()
            ->assertJsonPath('balance', 1250000)
            ->assertJsonPath('account.username', 'supplier-user')
            ->assertJsonMissingPath('account.api_key');
    }

    public function test_supplier_sync_returns_safe_bad_gateway_response_for_upstream_failure(): void
    {
        config()->set('services.ads7979.api_key', 'test-api-key');
        Http::fake([
            'https://ads7979.com/api/v2/categories' => Http::response([
                'internal_debug' => 'supplier database connection failed',
            ], 503),
        ]);
        $admin = UserModel::create([
            'username' => 'supplier_error_admin',
            'name' => 'Supplier Error Admin',
            'email' => 'supplier-error-admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'balance' => 0,
            'status' => 'active',
        ]);
        $token = app(JwtService::class)->generateToken([
            'sub' => $admin->id,
            'email' => $admin->email,
            'role' => 'admin',
            'ver' => 0,
        ], 1800);

        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/suppliers/ads7979/sync');

        $response->assertStatus(502)
            ->assertJsonPath('message', 'Supplier service is temporarily unavailable.')
            ->assertJsonStructure(['error_id'])
            ->assertJsonMissing(['internal_debug' => 'supplier database connection failed']);
        $this->assertSame('failed', SupplierModel::where('code', 'ads7979')->value('last_sync_status'));
    }

    private function fakeSupplierAndRateApis(): void
    {
        Cache::forget('usdt_to_vnd_rate');
        config()->set('services.ads7979.api_key', 'test-api-key');
        Http::fake([
            'https://ads7979.com/api/v2/categories' => Http::response($this->catalog()),
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26000],
            ]),
        ]);
    }

    private function adminToken(string $username): string
    {
        $admin = UserModel::create([
            'username' => $username,
            'name' => 'Supplier Admin',
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

    private function catalog(): array
    {
        return [[
            'id' => 1,
            'name' => 'BM50$',
            'image' => '/images/stores/BM_Old.jpg',
            'children' => [[
                'id' => 2,
                'name' => 'BM50$ Ngâm',
                'image' => '/images/stores/BM_Old.jpg',
                'quantity' => 17,
                'price' => 20000,
                'export_format' => ['uid', 'password'],
                'data' => [
                    'min_purchase' => '1',
                    'warranty_days' => '1',
                    'warranty' => 'Test warranty',
                    'description' => 'Test description',
                    'hdsd' => 'Test instructions',
                ],
            ]],
        ]];
    }
}
