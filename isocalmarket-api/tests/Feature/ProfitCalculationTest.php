<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitCalculationTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $adminUser;
    private UserModel $customerUser;
    private CategoryModel $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a771',
            'username' => 'admin_profit',
            'name' => 'System Admin',
            'email' => 'admin_profit@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 1000000.0,
        ]);

        $this->customerUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a772',
            'username' => 'customer_profit',
            'name' => 'Test Customer',
            'email' => 'customer_profit@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 500000.0,
        ]);

        $this->category = CategoryModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a773',
            'name' => 'Facebook Via',
            'slug' => 'facebook-via'
        ]);
    }

    private function adminHeaders(): array
    {
        $token = resolve(JwtService::class)->generateToken([
            'sub' => $this->adminUser->id,
            'email' => $this->adminUser->email,
            'role' => $this->adminUser->role,
        ], 1800);

        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ];
    }

    private function customerHeaders(): array
    {
        $token = resolve(JwtService::class)->generateToken([
            'sub' => $this->customerUser->id,
            'email' => $this->customerUser->email,
            'role' => $this->customerUser->role,
        ], 1800);

        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ];
    }

    public function test_import_inventory_batch_with_cost_price(): void
    {
        $product = ProductModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a774',
            'category_id' => $this->category->id,
            'name' => 'Via Viet 902',
            'slug' => 'via-viet-902',
            'price' => 50000.0,
            'status' => 'active'
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/v1/inventory/import', [
                'product_id' => $product->id,
                'batch_name' => 'Lô Nhập Via 902 Giá Vốn 20k',
                'cost_price' => 20000.0,
                'raw_data' => "via1|pass1|2fa1\nvia2|pass2|2fa2\nvia3|pass3|2fa3"
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.cost_price', 20000);

        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'cost_price' => 20000.0,
            'initial_count' => 3
        ]);
    }

    public function test_order_checkout_snapshots_cost_price_and_calculates_profit_correctly(): void
    {
        // 1. Tạo sản phẩm với giá bán 50k
        $product = ProductModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a775',
            'category_id' => $this->category->id,
            'name' => 'Clone Via Kháng',
            'slug' => 'clone-via-khang',
            'price' => 50000.0,
            'status' => 'active'
        ]);

        // 2. Nhập kho lô hàng với Giá vốn 20k
        $importResponse = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/v1/inventory/import', [
                'product_id' => $product->id,
                'batch_name' => 'Lô 10 Via Kháng',
                'cost_price' => 20000.0,
                'raw_data' => "nick1|pass1|2fa1\nnick2|pass2|2fa2\nnick3|pass3|2fa3"
            ]);
        $importResponse->assertStatus(201);

        // 3. Khách mua 2 cái -> Tổng Giá bán = 100k, Tổng Giá vốn = 40k, Lợi nhuận = 60k
        $checkoutResponse = $this->withHeaders($this->customerHeaders())
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2
                    ]
                ]
            ]);

        $checkoutResponse->assertStatus(201);

        // Kiểm tra Snapshot cost_price trong order_items
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'price' => 50000.0,
            'cost_price' => 20000.0,
            'quantity' => 2
        ]);

        // 4. Kiểm tra API Dashboard Stats
        $statsResponse = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/v1/admin/dashboard-stats');

        $statsResponse->assertStatus(200)
            ->assertJsonPath('total_revenue', 100000)
            ->assertJsonPath('total_cogs', 40000)
            ->assertJsonPath('total_profit', 60000)
            ->assertJsonPath('profit_margin_percent', 60);
    }
}
