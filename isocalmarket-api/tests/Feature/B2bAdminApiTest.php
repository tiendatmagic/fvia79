<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class B2bAdminApiTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $adminUser;

    private UserModel $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77a',
            'username' => 'admin_test',
            'name' => 'B2B Admin',
            'email' => 'admin_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 1000000.0,
            'api_key' => 'ADMIN-KEY-TEST-12345',
        ]);

        $this->normalUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77b',
            'username' => 'reseller_test',
            'name' => 'B2B Reseller',
            'email' => 'reseller_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 50000.0,
            'api_key' => 'RESELLER-KEY-TEST-12345',
        ]);
    }

    public function test_admin_can_fetch_b2b_users(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->getJson('/api/v1/admin/b2b/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'users' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'role',
                    'balance',
                    'has_api_key',
                ],
            ],
        ]);
    }

    public function test_admin_get_endpoint_accepts_api_key_in_query_string(): void
    {
        $this->getJson('/api/v1/admin/b2b/users?api_key=ADMIN-KEY-TEST-12345')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_b2b_pagination_is_bounded(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->getJson('/api/v1/admin/b2b/users?page=-5&limit=99999');

        $response->assertOk()
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.limit', 20);
    }

    public function test_normal_reseller_is_forbidden_to_fetch_b2b_users(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'RESELLER-KEY-TEST-12345',
        ])->getJson('/api/v1/admin/b2b/users');

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Forbidden. Admin access required.',
        ]);
    }

    public function test_unauthorized_if_api_key_missing_or_invalid(): void
    {
        $response1 = $this->getJson('/api/v1/admin/b2b/users');
        $response1->assertStatus(401);

        $response2 = $this->withHeaders([
            'X-API-KEY' => 'INVALID-KEY-123',
        ])->getJson('/api/v1/admin/b2b/users');
        $response2->assertStatus(401);
    }

    public function test_admin_can_fetch_b2b_orders(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->getJson('/api/v1/admin/b2b/orders');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'orders',
        ]);
    }

    public function test_reseller_can_request_own_b2b_order_detail(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'RESELLER-KEY-TEST-12345',
        ])->getJson('/api/v1/b2b/orders/ORDER-DOES-NOT-EXIST');

        $response->assertNotFound()
            ->assertJson(['message' => 'Order not found.']);
    }

    public function test_admin_can_import_inventory(): void
    {
        $category = CategoryModel::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description',
        ]);

        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test Description',
            'price' => 10000.0,
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->postJson("/api/v1/admin/b2b/products/{$product->id}/inventory", [
            'batch_name' => 'Lô hàng test',
            'content' => "tài khoản 1\ntài khoản 2",
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $file = UploadedFile::fake()->createWithContent('inventory.txt', "tài khoản 3\ntài khoản 4");

        $responseFile = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->post("/api/v1/admin/b2b/products/{$product->id}/inventory", [
            'batch_name' => 'Lô hàng test file',
            'file' => $file,
        ]);

        $responseFile->assertStatus(201);
        $responseFile->assertJson([
            'success' => true,
        ]);
    }

    public function test_admin_inventory_import_rejects_oversized_files(): void
    {
        $category = CategoryModel::create([
            'name' => 'Limited Upload Category',
            'slug' => 'limited-upload-category',
        ]);
        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'Limited Upload Product',
            'slug' => 'limited-upload-product',
            'price' => 1,
            'status' => 'active',
        ]);
        $file = UploadedFile::fake()->create('too-large.txt', 5121, 'text/plain');

        $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->post("/api/v1/admin/b2b/products/{$product->id}/inventory", [
            'file' => $file,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseCount('inventory_batches', 0);
    }

    public function test_admin_can_fetch_b2b_dashboard_stats(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->getJson('/api/v1/admin/b2b/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_revenue',
                'total_cogs',
                'total_profit',
                'profit_margin_percent',
                'total_orders',
                'total_products',
                'total_available_inventory',
                'total_users',
            ],
        ]);
    }

    public function test_b2b_dashboard_inventory_combines_supplier_and_local_stock(): void
    {
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update([
            'enabled' => true,
            'products_enabled' => true,
        ]);
        $category = CategoryModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'stats-category',
            'name' => 'Stats Category',
            'slug' => 'stats-category',
        ]);
        $supplierProduct = ProductModel::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'stats-supplier-product',
            'category_id' => $category->id,
            'name' => 'Supplier Stats Product',
            'slug' => 'supplier-stats-product',
            'price' => 1,
            'supplier_stock' => 7,
            'status' => 'active',
        ]);
        InventoryBatchModel::create([
            'product_id' => $supplierProduct->id,
            'batch_name' => 'Local Stats Stock',
            'initial_count' => 3,
            'sold_count' => 1,
            'status' => 'active',
        ]);

        $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345',
        ])->getJson('/api/v1/admin/b2b/stats')
            ->assertOk()
            ->assertJsonPath('data.total_available_inventory', 9);
    }

    public function test_reseller_cannot_fetch_b2b_dashboard_stats(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'RESELLER-KEY-TEST-12345',
        ])->getJson('/api/v1/admin/b2b/stats');

        $response->assertStatus(403);
    }
}
