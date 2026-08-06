<?php

namespace Tests\Feature;

use App\Domain\Repositories\DepositRepositoryInterface;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NPlusOneQueryRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_grouped_catalog_query_count_does_not_grow_with_categories(): void
    {
        $this->createCatalogProducts(2, 'small');
        $smallQueryCount = $this->countQueries(
            fn () => app(ProductRepositoryInterface::class)->findGroupedActiveProducts(5),
        );

        $this->createCatalogProducts(8, 'large');
        $largeQueryCount = $this->countQueries(
            fn () => app(ProductRepositoryInterface::class)->findGroupedActiveProducts(5),
        );

        $this->assertLessThanOrEqual($smallQueryCount + 1, $largeQueryCount);
        $this->assertLessThanOrEqual(4, $largeQueryCount);
    }

    public function test_b2b_product_query_count_does_not_grow_with_page_size(): void
    {
        $apiKey = 'n-plus-one-b2b-key';
        UserModel::create([
            'username' => 'n_plus_one_b2b',
            'name' => 'N+1 B2B User',
            'email' => 'n-plus-one-b2b@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 0,
            'status' => 'active',
            'api_key' => $apiKey,
        ]);
        $category = $this->createCategory('b2b');
        $this->createProduct($category->id, 'b2b-0');

        $smallQueryCount = $this->countQueries(function () use ($apiKey) {
            $this->withHeader('X-API-KEY', $apiKey)
                ->getJson('/api/v1/b2b/products?limit=20')
                ->assertOk();
        });

        foreach (range(1, 9) as $index) {
            $this->createProduct($category->id, "b2b-{$index}");
        }

        $largeQueryCount = $this->countQueries(function () use ($apiKey) {
            $this->withHeader('X-API-KEY', $apiKey)
                ->getJson('/api/v1/b2b/products?limit=20')
                ->assertOk()
                ->assertJsonCount(10, 'products');
        });

        $this->assertLessThanOrEqual($smallQueryCount + 1, $largeQueryCount);
    }

    public function test_inventory_batch_query_count_does_not_grow_with_page_size(): void
    {
        $category = $this->createCategory('inventory');
        $product = $this->createProduct($category->id, 'inventory');
        $this->createInventoryBatches($product, 2, 'small');

        $smallQueryCount = $this->countQueries(
            fn () => app(InventoryBatchRepositoryInterface::class)->findAllPaginated([], 1, 20),
        );

        $this->createInventoryBatches($product, 8, 'large');
        $largeQueryCount = $this->countQueries(
            fn () => app(InventoryBatchRepositoryInterface::class)->findAllPaginated([], 1, 20),
        );

        $this->assertSame($smallQueryCount, $largeQueryCount);
        $this->assertLessThanOrEqual(3, $largeQueryCount);
    }

    public function test_deposit_history_query_count_does_not_grow_with_page_size(): void
    {
        $user = UserModel::create([
            'username' => 'n_plus_one_deposit',
            'name' => 'N+1 Deposit User',
            'email' => 'n-plus-one-deposit@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 0,
            'status' => 'active',
        ]);
        $this->createDeposits($user->id, 1, 0);

        $smallQueryCount = $this->countQueries(
            fn () => app(DepositRepositoryInterface::class)->findByUserIdPaginated($user->id, 1, 20),
        );

        $this->createDeposits($user->id, 9, 1);
        $largeQueryCount = $this->countQueries(
            fn () => app(DepositRepositoryInterface::class)->findByUserIdPaginated($user->id, 1, 20),
        );

        $this->assertSame($smallQueryCount, $largeQueryCount);
        $this->assertLessThanOrEqual(3, $largeQueryCount);
    }

    private function countQueries(callable $callback): int
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        try {
            $callback();

            return count(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }

    private function createCatalogProducts(int $count, string $prefix): void
    {
        foreach (range(1, $count) as $index) {
            $category = $this->createCategory("{$prefix}-{$index}");
            $this->createProduct($category->id, "{$prefix}-{$index}");
        }
    }

    private function createCategory(string $suffix): CategoryModel
    {
        return CategoryModel::create([
            'name' => "Category {$suffix}",
            'name_en' => "Category {$suffix}",
            'name_vi' => "Danh muc {$suffix}",
            'slug' => "category-{$suffix}",
            'is_active' => true,
        ]);
    }

    private function createProduct(string $categoryId, string $suffix): ProductModel
    {
        return ProductModel::create([
            'category_id' => $categoryId,
            'name' => "Product {$suffix}",
            'name_en' => "Product {$suffix}",
            'name_vi' => "San pham {$suffix}",
            'slug' => "product-{$suffix}",
            'price' => 1,
            'supplier_stock' => 1,
            'status' => 'active',
            'attributes' => [],
        ]);
    }

    private function createInventoryBatches(ProductModel $product, int $count, string $prefix): void
    {
        foreach (range(1, $count) as $index) {
            $batch = InventoryBatchModel::create([
                'product_id' => $product->id,
                'batch_name' => "Batch {$prefix} {$index}",
                'initial_count' => 1,
                'sold_count' => 0,
                'status' => 'active',
            ]);
            InventoryItemModel::create([
                'batch_id' => $batch->id,
                'product_id' => $product->id,
                'secret_data' => "resource-{$prefix}-{$index}",
                'status' => 'available',
            ]);
        }
    }

    private function createDeposits(string $userId, int $count, int $offset): void
    {
        foreach (range(1, $count) as $index) {
            $number = $offset + $index;
            DepositModel::create([
                'user_id' => $userId,
                'code' => "DEP-N1-{$number}",
                'amount_usdt' => 10,
                'amount_to_pay' => 10,
                'amount_vnd' => 250000,
                'status' => 'pending_payment',
                'payment_method' => 'bep20',
            ]);
        }
    }
}
