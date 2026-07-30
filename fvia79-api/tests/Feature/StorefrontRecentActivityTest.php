<?php

namespace Tests\Feature;

use App\Application\Services\SystemSettingService;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontRecentActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_mode_uses_the_actual_order_and_deposit_columns(): void
    {
        $settings = app(SystemSettingService::class);
        $settings->saveSettings([
            'recent_purchases_enabled' => '1',
            'recent_purchases_mode' => 'real',
            'recent_deposits_enabled' => '1',
            'recent_deposits_mode' => 'real',
        ]);
        $user = UserModel::create([
            'username' => 'storefront_customer',
            'name' => 'Storefront Customer',
            'email' => 'storefront-customer@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 0,
            'status' => 'active',
        ]);
        $supplier = SupplierModel::firstOrCreate(
            ['code' => 'fvia7979'],
            ['name' => 'FVIA79 Supplier', 'status' => 'active', 'base_url' => 'https://example.com']
        );
        $category = CategoryModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'storefront-activity',
            'name' => 'Storefront Activity',
            'slug' => 'storefront-activity',
        ]);
        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'Storefront Product',
            'name_vi' => 'Sản phẩm Storefront',
            'name_en' => 'Storefront Product',
            'slug' => 'storefront-product',
            'price' => 12.345678,
            'supplier_price_vnd' => 0,
            'supplier_stock' => 1,
            'status' => 'active',
            'attributes' => [],
        ]);
        $order = OrderModel::create([
            'user_id' => $user->id,
            'order_code' => 'ORD-STOREFRONT-1',
            'total_price' => 12.345678,
            'status' => 'paid',
        ]);
        OrderItemModel::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'cost_price' => 1,
            'quantity' => 1,
        ]);
        DepositModel::create([
            'user_id' => $user->id,
            'code' => 'DEP-STOREFRONT-1',
            'amount_usdt' => 10,
            'amount_to_pay' => 10,
            'amount_vnd' => 265000,
            'status' => 'completed',
            'payment_method' => 'usdt_bep20',
        ]);

        $this->getJson('/api/v1/storefront/recent-purchases')
            ->assertOk()
            ->assertJsonPath('mode', 'real')
            ->assertJsonPath('data.0.total_price', 12.345678)
            ->assertJsonPath('data.0.total_usd', 12.345678);

        $this->getJson('/api/v1/storefront/recent-deposits')
            ->assertOk()
            ->assertJsonPath('mode', 'real')
            ->assertJsonPath('data.0.amount_usdt', 10)
            ->assertJsonPath('data.0.amount_vnd', 265000);
    }

    public function test_default_state_is_disabled_when_settings_unconfigured(): void
    {
        $this->getJson('/api/v1/storefront/recent-purchases')
            ->assertOk()
            ->assertJsonPath('enabled', false);

        $this->getJson('/api/v1/storefront/recent-deposits')
            ->assertOk()
            ->assertJsonPath('enabled', false);
    }
}
