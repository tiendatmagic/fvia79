<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierSaveConfigTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(string $username = 'admin_supplier_test'): string
    {
        $admin = UserModel::create([
            'username' => $username,
            'name' => 'Admin Supplier Test',
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

    public function test_save_supplier_config_updates_settings_without_driver_override(): void
    {
        $token = $this->adminToken('admin_save_1');

        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update([
            'api_key' => 'existing_encrypted_key',
            'catalog_driver' => 'clone_store_v2',
            'enabled' => true,
        ]);

        $payload = [
            'name' => 'ADS7979 Renamed',
            'base_url' => 'https://ads7979.com',
            'catalog_driver' => 'clone_store_v2',
            'enabled' => true,
            'products_enabled' => true,
            'import_out_of_stock' => true,
            'auto_sync' => true,
            'auto_translate' => true,
            'target_language' => 'en',
            'sync_interval_minutes' => 10,
            'price_adjustment_type' => 'percent',
            'price_adjustment_value' => 15,
            'round_vnd_prices' => true,
            'auto_hide_out_of_stock' => true,
        ];

        $response = $this->withToken($token)
            ->putJson("/api/v1/admin/suppliers/{$supplier->code}", $payload);

        $response->assertOk()
            ->assertJsonPath('name', 'ADS7979 Renamed')
            ->assertJsonPath('catalog_driver', 'clone_store_v2')
            ->assertJsonPath('sync_interval_minutes', 10)
            ->assertJsonPath('price_adjustment_type', 'percent')
            ->assertJsonPath('price_adjustment_value', 15)
            ->assertJsonPath('round_vnd_prices', true)
            ->assertJsonPath('auto_hide_out_of_stock', true)
            ->assertJsonPath('target_language', 'en');

        $supplier->refresh();
        $this->assertSame('ADS7979 Renamed', $supplier->name);
        $this->assertSame('clone_store_v2', $supplier->catalog_driver);
        $this->assertSame('existing_encrypted_key', $supplier->api_key);
        $this->assertSame(10, $supplier->sync_interval_minutes);
        $this->assertSame(15.0, (float) $supplier->price_adjustment_value);
        $this->assertTrue($supplier->round_vnd_prices);
    }

    public function test_save_supplier_with_new_api_key_trims_and_encrypts(): void
    {
        $token = $this->adminToken('admin_save_2');

        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update([
            'api_key' => 'old_key',
            'catalog_driver' => 'clone_store_v2',
            'enabled' => true,
        ]);

        $payload = [
            'name' => 'ADS7979',
            'base_url' => 'https://ads7979.com',
            'api_key' => '  new_secret_key_123  ',
            'catalog_driver' => 'clone_store_v2',
        ];

        $response = $this->withToken($token)
            ->putJson("/api/v1/admin/suppliers/{$supplier->code}", $payload);

        $response->assertOk()
            ->assertJsonPath('has_api_key', true);

        $supplier->refresh();
        $this->assertSame('new_secret_key_123', $supplier->api_key);
    }

    public function test_store_supplier_with_explicit_template_driver(): void
    {
        $token = $this->adminToken('admin_save_3');

        $payload = [
            'name' => 'MUABM365 Store',
            'base_url' => 'https://muabm365.top',
            'api_key' => 'mbm_test_key_xyz',
            'catalog_driver' => 'muabm365',
            'enabled' => true,
            'sync_interval_minutes' => 5,
        ];

        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/suppliers', $payload);

        $response->assertCreated()
            ->assertJsonPath('name', 'MUABM365 Store')
            ->assertJsonPath('catalog_driver', 'muabm365')
            ->assertJsonPath('sync_interval_minutes', 5);

        $created = SupplierModel::where('name', 'MUABM365 Store')->first();
        $this->assertNotNull($created);
        $this->assertSame('muabm365', $created->catalog_driver);
        $this->assertSame('mbm_test_key_xyz', $created->api_key);
    }
}
