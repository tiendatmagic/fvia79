<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class B2bAdminApiTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $adminUser;
    private UserModel $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin user
        $this->adminUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77a',
            'name' => 'B2B Admin',
            'email' => 'admin_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 1000000.0,
            'api_key' => 'ADMIN-KEY-TEST-12345'
        ]);

        // Create normal Reseller user
        $this->normalUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77b',
            'name' => 'B2B Reseller',
            'email' => 'reseller_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 50000.0,
            'api_key' => 'RESELLER-KEY-TEST-12345'
        ]);
    }

    public function test_admin_can_fetch_b2b_users(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345'
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
                    'has_api_key'
                ]
            ]
        ]);
    }

    public function test_normal_reseller_is_forbidden_to_fetch_b2b_users(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'RESELLER-KEY-TEST-12345'
        ])->getJson('/api/v1/admin/b2b/users');

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Forbidden. Admin access required.'
        ]);
    }

    public function test_unauthorized_if_api_key_missing_or_invalid(): void
    {
        // Missing API Key
        $response1 = $this->getJson('/api/v1/admin/b2b/users');
        $response1->assertStatus(401);

        // Invalid API Key
        $response2 = $this->withHeaders([
            'X-API-KEY' => 'INVALID-KEY-123'
        ])->getJson('/api/v1/admin/b2b/users');
        $response2->assertStatus(401);
    }

    public function test_admin_can_fetch_b2b_orders(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345'
        ])->getJson('/api/v1/admin/b2b/orders');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'orders'
        ]);
    }

    public function test_admin_can_import_inventory(): void
    {
        $category = CategoryModel::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description'
        ]);

        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test Description',
            'price' => 10000.0,
            'status' => 'active'
        ]);

        // Test 1: Import via JSON payload 'content'
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345'
        ])->postJson("/api/v1/admin/b2b/products/{$product->id}/inventory", [
            'batch_name' => 'Lô hàng test',
            'content' => "tài khoản 1\ntài khoản 2"
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true
        ]);

        // Test 2: Import via file upload
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('inventory.txt', "tài khoản 3\ntài khoản 4");
        
        $responseFile = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345'
        ])->post("/api/v1/admin/b2b/products/{$product->id}/inventory", [
            'batch_name' => 'Lô hàng test file',
            'file' => $file
        ]);

        $responseFile->assertStatus(201);
        $responseFile->assertJson([
            'success' => true
        ]);
    }

    public function test_admin_can_fetch_b2b_dashboard_stats(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'ADMIN-KEY-TEST-12345'
        ])->getJson('/api/v1/admin/b2b/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_revenue',
                'total_orders',
                'total_products',
                'total_available_inventory',
                'total_users'
            ]
        ]);
    }

    public function test_reseller_cannot_fetch_b2b_dashboard_stats(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'RESELLER-KEY-TEST-12345'
        ])->getJson('/api/v1/admin/b2b/stats');

        $response->assertStatus(403);
    }
}
