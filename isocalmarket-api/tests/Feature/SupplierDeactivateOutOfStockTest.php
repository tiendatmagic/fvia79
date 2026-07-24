<?php

namespace Tests\Feature;

use App\Application\Services\AuthService;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupplierDeactivateOutOfStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivate_out_of_stock_products_updates_only_zero_stock_active_products(): void
    {
        $admin = UserModel::create([
            'username' => 'admin_test_supplier',
            'name' => 'Admin Supplier Tester',
            'email' => 'admin_test_supplier@example.com',
            'password' => Hash::make('Password123!@#'),
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
        ]);

        $domainUser = UserMapper::toDomain($admin);
        $authService = app(AuthService::class);
        $session = $authService->createSession($domainUser, '127.0.0.1', 'TestAgent');
        $token = $authService->generateAccessToken($domainUser, $session->id);

        $supplier = SupplierModel::create([
            'code' => 'test-supplier',
            'name' => 'Test Supplier',
            'base_url' => 'https://test-supplier.com',
            'catalog_driver' => 'clone_store_v1',
            'api_key' => 'secret_key',
            'enabled' => true,
            'products_enabled' => true,
        ]);

        $productOutOfStock = ProductModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'p1',
            'name' => 'Out of Stock Product',
            'slug' => 'out-of-stock-product',
            'price' => 10,
            'supplier_price_vnd' => 10000,
            'supplier_stock' => 0,
            'status' => 'active',
        ]);

        $productInStock = ProductModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'p2',
            'name' => 'In Stock Product',
            'slug' => 'in-stock-product',
            'price' => 20,
            'supplier_price_vnd' => 20000,
            'supplier_stock' => 15,
            'status' => 'active',
        ]);

        $productAlreadyInactive = ProductModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'p3',
            'name' => 'Already Inactive Product',
            'slug' => 'already-inactive-product',
            'price' => 30,
            'supplier_price_vnd' => 30000,
            'supplier_stock' => 0,
            'status' => 'inactive',
        ]);

        $response = $this->withToken($token)
            ->postJson("/api/v1/admin/suppliers/{$supplier->code}/deactivate-out-of-stock-products");

        $response->assertStatus(200)
            ->assertJson([
                'count' => 1,
            ]);

        $this->assertSame('inactive', $productOutOfStock->fresh()->status);
        $this->assertSame('active', $productInStock->fresh()->status);
        $this->assertSame('inactive', $productAlreadyInactive->fresh()->status);
    }
}
