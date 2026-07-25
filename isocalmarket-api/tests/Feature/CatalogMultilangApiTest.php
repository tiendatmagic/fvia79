<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogMultilangApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_crud_accepts_english_fields_and_fills_missing_vietnamese_fields(): void
    {
        $headers = ['Authorization' => 'Bearer '.$this->adminToken()];

        $categoryResponse = $this->withHeaders($headers)->postJson('/api/v1/categories', [
            'name_en' => 'Digital Accounts',
            'description_en' => 'English category description',
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('category.name', 'Digital Accounts')
            ->assertJsonPath('category.name_en', 'Digital Accounts')
            ->assertJsonPath('category.name_vi', 'Digital Accounts');

        $categoryId = $categoryResponse->json('category.id');
        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
            'name' => 'Digital Accounts',
            'name_en' => 'Digital Accounts',
            'name_vi' => 'Digital Accounts',
        ]);

        $productResponse = $this->withHeaders($headers)->postJson('/api/v1/products', [
            'category_id' => $categoryId,
            'name_en' => 'Premium Account',
            'description_en' => 'English product description',
            'price' => 1.25,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('product.name', 'Premium Account')
            ->assertJsonPath('product.name_vi', 'Premium Account');

        $productId = $productResponse->json('product.id');
        $this->withHeaders($headers)->putJson("/api/v1/products/{$productId}?lang=vi", [
            'category_id' => $categoryId,
            'name_en' => 'Premium Account Updated',
            'name_vi' => 'Tài khoản cao cấp',
            'description_en' => 'Updated English description',
            'description_vi' => 'Mô tả tiếng Việt',
            'price' => 1.5,
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('product.name', 'Tài khoản cao cấp')
            ->assertJsonPath('product.description', 'Mô tả tiếng Việt');

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'name' => 'Premium Account Updated',
            'name_en' => 'Premium Account Updated',
            'name_vi' => 'Tài khoản cao cấp',
        ]);

        $this->withHeaders($headers)->deleteJson("/api/v1/products/{$productId}")
            ->assertOk();
        $this->assertSoftDeleted('products', ['id' => $productId]);

        $emptyCategory = CategoryModel::create([
            'name' => 'Empty Category',
            'name_en' => 'Empty Category',
            'name_vi' => 'Danh mục trống',
            'slug' => 'empty-category',
            'is_active' => true,
        ]);
        $this->withHeaders($headers)->deleteJson("/api/v1/categories/{$emptyCategory->id}")
            ->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $emptyCategory->id]);
    }

    public function test_public_catalog_localizes_cached_products_and_invalidates_localized_category_cache(): void
    {
        $category = CategoryModel::create([
            'name' => 'Games',
            'name_en' => 'Games',
            'name_vi' => 'Trò chơi',
            'slug' => 'games',
            'description' => 'English category',
            'description_en' => 'English category',
            'description_vi' => 'Danh mục tiếng Việt',
            'is_active' => true,
        ]);
        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'Game Account',
            'name_en' => 'Game Account',
            'name_vi' => 'Tài khoản game',
            'slug' => 'game-account',
            'description' => 'English description',
            'description_en' => 'English description',
            'description_vi' => 'Mô tả tiếng Việt',
            'price' => 2,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/products?status=active')
            ->assertOk()
            ->assertJsonPath('0.name', 'Game Account');
        $this->withHeader('Accept-Language', 'vi-VN,vi;q=0.9')
            ->getJson('/api/v1/products?status=active')
            ->assertOk()
            ->assertJsonPath('0.name', 'Tài khoản game')
            ->assertJsonPath('0.description', 'Mô tả tiếng Việt');
        $this->getJson('/api/v1/products/grouped?lang=vi')
            ->assertOk()
            ->assertJsonPath('0.name', 'Trò chơi')
            ->assertJsonPath('0.products.0.name', 'Tài khoản game');

        $this->getJson('/api/v1/categories?lang=vi')
            ->assertOk()
            ->assertJsonPath('0.name', 'Trò chơi');

        $headers = ['Authorization' => 'Bearer '.$this->adminToken()];
        $this->withHeaders($headers)->putJson("/api/v1/categories/{$category->id}", [
            'name_en' => 'Updated Games',
            'name_vi' => 'Trò chơi mới',
            'description_en' => 'Updated',
            'description_vi' => 'Đã cập nhật',
            'is_active' => true,
        ])->assertOk();

        $this->getJson('/api/v1/categories?lang=vi')
            ->assertOk()
            ->assertJsonPath('0.name', 'Trò chơi mới');

        $this->assertNotNull($product->fresh());
    }

    public function test_b2b_product_endpoints_honor_query_and_accept_language(): void
    {
        $customer = UserModel::create([
            'username' => 'multilang_b2b',
            'name' => 'Multilang B2B',
            'email' => 'multilang-b2b@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 10,
            'status' => 'active',
            'api_key' => 'MULTILANG-B2B-KEY',
        ]);
        $category = CategoryModel::create([
            'name' => 'Social Accounts',
            'name_en' => 'Social Accounts',
            'name_vi' => 'Tài khoản mạng xã hội',
            'slug' => 'social-accounts',
            'is_active' => true,
        ]);
        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'Social Account',
            'name_en' => 'Social Account',
            'name_vi' => 'Tài khoản mạng xã hội',
            'slug' => 'social-account',
            'description' => 'English details',
            'description_en' => 'English details',
            'description_vi' => 'Chi tiết tiếng Việt',
            'price' => 3,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/b2b/products?api_key='.$customer->api_key.'&lang=vi')
            ->assertOk()
            ->assertJsonPath('products.0.title', 'Tài khoản mạng xã hội')
            ->assertJsonPath('products.0.category', 'Tài khoản mạng xã hội');

        $this->withHeader('Accept-Language', 'vi-VN')
            ->getJson("/api/v1/b2b/products/{$product->id}?api_key={$customer->api_key}")
            ->assertOk()
            ->assertJsonPath('title', 'Tài khoản mạng xã hội')
            ->assertJsonPath('description', 'Chi tiết tiếng Việt');
    }

    public function test_grouped_products_search_filters_out_empty_categories(): void
    {
        $catEmpty = CategoryModel::create([
            'name' => 'Empty Cat',
            'slug' => 'empty-cat',
            'name_vi' => 'Danh mục rỗng',
            'name_en' => 'Empty Cat',
            'is_active' => true,
        ]);
        $catWithProd = CategoryModel::create([
            'name' => 'Active Cat',
            'slug' => 'active-cat',
            'name_vi' => 'Danh mục có hàng',
            'name_en' => 'Active Cat',
            'is_active' => true,
        ]);

        $prodInStock = ProductModel::create([
            'category_id' => $catWithProd->id,
            'name' => 'Special Search Target',
            'name_vi' => 'Sản phẩm đặc biệt',
            'name_en' => 'Special Search Target',
            'slug' => 'special-search-target',
            'price' => 10,
            'status' => 'active',
        ]);
        \App\Infrastructure\Persistence\Eloquent\InventoryBatchModel::create([
            'batch_name' => 'BATCH001',
            'product_id' => $prodInStock->id,
            'initial_count' => 10,
            'sold_count' => 0,
            'status' => 'active',
        ]);

        $prodOutOfStock = ProductModel::create([
            'category_id' => $catEmpty->id,
            'name' => 'Special Out Of Stock',
            'name_vi' => 'Sản phẩm hết hàng',
            'name_en' => 'Special Out Of Stock',
            'slug' => 'special-out-of-stock',
            'price' => 10,
            'status' => 'active',
        ]);

        // With include_out_of_stock=0 (default), out-of-stock product is excluded
        $resDefault = $this->getJson('/api/v1/products/grouped?search=Special')
            ->assertOk()
            ->json();

        $this->assertCount(1, $resDefault);
        $this->assertEquals($catWithProd->id, $resDefault[0]['id']);
        $this->assertEquals('Special Search Target', $resDefault[0]['products'][0]['name']);

        // With include_out_of_stock=1, both categories with products are returned
        $resWithOos = $this->getJson('/api/v1/products/grouped?search=Special&include_out_of_stock=1')
            ->assertOk()
            ->json();

        $this->assertCount(2, $resWithOos);
    }

    private function adminToken(): string
    {
        $admin = UserModel::create([
            'username' => 'catalog_multilang_admin_'.str()->random(6),
            'name' => 'Catalog Admin',
            'email' => str()->random(8).'@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'balance' => 0,
            'status' => 'active',
        ]);

        return app(JwtService::class)->generateToken([
            'sub' => $admin->id,
            'email' => $admin->email,
            'role' => 'admin',
            'ver' => 0,
        ], 1800);
    }
}
