<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\SupplierPurchaseModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class B2bSupplierOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'B2B-SUPPLIER-ORDER-TEST-KEY';

    private UserModel $customer;

    private SupplierModel $supplier;

    private CategoryModel $category;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('usdt_to_vnd_rate');
        $this->customer = UserModel::create([
            'username' => 'b2b_supplier_customer',
            'name' => 'B2B Supplier Customer',
            'email' => 'b2b-supplier@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 100,
            'status' => 'active',
            'api_key' => self::API_KEY,
        ]);
        $this->supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $this->supplier->update([
            'api_key' => 'supplier-test-key',
            'catalog_driver' => 'clone_store_v2',
            'enabled' => true,
            'products_enabled' => true,
        ]);
        $this->category = CategoryModel::create([
            'supplier_id' => $this->supplier->id,
            'external_id' => 'b2b-category',
            'name' => 'B2B Category',
            'slug' => 'b2b-category',
        ]);
    }

    public function test_b2b_buy_uses_supplier_fulfillment_and_is_idempotent(): void
    {
        $product = $this->supplierProduct(stock: 2);
        $this->fakeSuccessfulSupplierOrder();
        $payload = [
            'idempotency_key' => '019f906d-a49e-7e91-9331-589e9942bf21',
            'product_id' => $product->id,
            'quantity' => 1,
        ];

        $first = $this->withApiKey()->postJson('/api/v1/b2b/buy', $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'paid')
            ->assertJsonPath('accounts.0', 'supplier-account-1');
        $second = $this->withApiKey()->postJson('/api/v1/b2b/buy', $payload)
            ->assertOk()
            ->assertJsonPath('order_code', $first->json('order_code'));

        $this->assertSame(1, SupplierPurchaseModel::count());
        $this->assertSame(1, $product->fresh()->supplier_stock);
        Http::assertSentCount(3);
    }

    public function test_b2b_buy_uses_only_local_inventory_for_manual_product_and_is_idempotent(): void
    {
        $product = ProductModel::create([
            'supplier_id' => null,
            'external_id' => null,
            'category_id' => $this->category->id,
            'name' => 'Sản phẩm demo',
            'slug' => 'manual-b2b-demo-product',
            'price' => 1,
            'supplier_price_vnd' => 0,
            'supplier_stock' => 0,
            'status' => 'active',
            'attributes' => [],
        ]);
        $batch = InventoryBatchModel::create([
            'product_id' => $product->id,
            'batch_name' => 'Manual B2B stock',
            'cost_price' => 0.40,
            'initial_count' => 2,
            'sold_count' => 0,
            'status' => 'active',
        ]);
        foreach (['manual-account-1', 'manual-account-2'] as $resource) {
            InventoryItemModel::create([
                'batch_id' => $batch->id,
                'product_id' => $product->id,
                'secret_data' => $resource,
                'status' => 'available',
            ]);
        }

        $headers = [
            'X-API-KEY' => self::API_KEY,
            'Idempotency-Key' => '019f906d-a49e-7e91-9331-589e9942bf28',
        ];
        $payload = ['product_id' => $product->id, 'quantity' => 1];
        $first = $this->withHeaders($headers)->postJson('/api/v1/b2b/buy', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'paid')
            ->assertJsonPath('total_price', 1)
            ->assertJsonPath('accounts.0', 'manual-account-1');
        $this->withHeaders($headers)->postJson('/api/v1/b2b/buy', $payload)
            ->assertOk()
            ->assertJsonPath('order_code', $first->json('order_code'));

        $order = OrderModel::where('order_code', $first->json('order_code'))->firstOrFail();
        $item = OrderItemModel::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(99.0, (float) $this->customer->fresh()->balance);
        $this->assertSame(1, OrderModel::where('idempotency_key', $headers['Idempotency-Key'])->count());
        $this->assertSame(0, SupplierPurchaseModel::where('order_id', $order->id)->count());
        $this->assertSame(0, $item->supplier_quantity);
        $this->assertSame(1, $item->local_quantity);
        $this->assertSame(0.40, (float) $item->cost_price);
        $this->assertSame(1, $batch->fresh()->sold_count);
        $this->assertSame(1, InventoryItemModel::where('batch_id', $batch->id)
            ->where('status', 'available')->count());
    }

    public function test_b2b_buy_debits_the_full_eight_decimal_order_total(): void
    {
        $product = $this->supplierProduct(stock: 1);
        $product->update(['price' => 0.01334804]);
        $this->fakeSuccessfulSupplierOrder();

        $this->withApiKey()->postJson('/api/v1/b2b/buy', [
            'idempotency_key' => '019f906d-a49e-7e91-9331-589e9942bf27',
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk()
            ->assertJsonPath('total_price', 0.01334804);

        $this->assertSame(99.98665196, (float) $this->customer->fresh()->balance);
    }

    public function test_b2b_products_list_matches_documented_response(): void
    {
        $product = $this->supplierProduct(stock: 7);

        $this->getJson('/api/v1/b2b/products?api_key='.self::API_KEY)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonPath('products.0.title', 'B2B Supplier Product')
            ->assertJsonPath('products.0.category', 'B2B Category')
            ->assertJsonPath('products.0.price', 0.02)
            ->assertJsonPath('products.0.stock', 7)
            ->assertJsonStructure([
                'pagination' => ['total', 'page', 'limit', 'last_page'],
            ]);
    }

    public function test_b2b_product_detail_matches_documented_response(): void
    {
        $product = $this->supplierProduct(stock: 4);

        $this->withApiKey()
            ->getJson('/api/v1/b2b/products/'.$product->id)
            ->assertOk()
            ->assertJson([
                'id' => $product->id,
                'title' => 'B2B Supplier Product',
                'price' => 0.02,
                'stock' => 4,
                'category' => 'B2B Category',
            ])
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'price',
                'stock',
                'category',
            ]);
    }

    public function test_b2b_buy_requires_an_idempotency_key(): void
    {
        $product = $this->supplierProduct(stock: 1);

        $this->withApiKey()->postJson('/api/v1/b2b/buy', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('idempotency_key');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_b2b_buy_rejects_excessive_quantity(): void
    {
        $product = $this->supplierProduct(stock: 2000);

        $this->withApiKey()->postJson('/api/v1/b2b/buy', [
            'idempotency_key' => '019f906d-a49e-7e91-9331-589e9942bf25',
            'product_id' => $product->id,
            'quantity' => 1001,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_b2b_buy_accepts_idempotency_header(): void
    {
        $product = $this->supplierProduct(stock: 1);
        $this->fakeSuccessfulSupplierOrder();

        $this->withHeaders([
            'X-API-KEY' => self::API_KEY,
            'Idempotency-Key' => '019f906d-a49e-7e91-9331-589e9942bf22',
        ])->postJson('/api/v1/b2b/buy', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk()
            ->assertJsonPath('status', 'paid');
    }

    public function test_b2b_buy_rejects_reusing_key_for_a_different_request(): void
    {
        $product = $this->supplierProduct(stock: 2);
        $this->fakeSuccessfulSupplierOrder();
        $key = '019f906d-a49e-7e91-9331-589e9942bf24';

        $this->withApiKey()->postJson('/api/v1/b2b/buy', [
            'idempotency_key' => $key,
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $this->withApiKey()->postJson('/api/v1/b2b/buy', [
            'idempotency_key' => $key,
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertBadRequest()
            ->assertJsonPath(
                'message',
                'The idempotency key has already been used for a different order request.',
            );

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(1, SupplierPurchaseModel::count());
        Http::assertSentCount(3);
    }

    public function test_b2b_pending_supplier_order_returns_accepted_not_success(): void
    {
        $product = $this->supplierProduct(stock: 1);
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::failedConnection(),
        ]);

        $this->withApiKey()->postJson('/api/v1/b2b/buy', [
            'idempotency_key' => '019f906d-a49e-7e91-9331-589e9942bf23',
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertAccepted()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('accounts', []);

        $this->assertDatabaseHas('orders', ['status' => 'pending']);
        $this->assertDatabaseHas('supplier_purchases', ['status' => 'unknown']);
    }

    public function test_get_endpoints_accept_api_key_in_query_string(): void
    {
        $this->getJson('/api/v1/b2b/me?api_key='.self::API_KEY)
            ->assertOk()
            ->assertJsonPath('id', $this->customer->id);
    }

    public function test_order_lookup_accepts_api_key_in_query_string(): void
    {
        $order = OrderModel::create([
            'user_id' => $this->customer->id,
            'order_code' => 'ORD-B2B-QUERY-KEY',
            'total_price' => 1.25,
            'discount_amount' => 0,
            'status' => 'paid',
        ]);

        $this->getJson('/api/v1/b2b/orders/'.$order->order_code.'?api_key='.self::API_KEY)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order_code', 'ORD-B2B-QUERY-KEY')
            ->assertJsonPath('status', 'paid');
    }

    public function test_post_endpoints_reject_api_key_in_query_string(): void
    {
        $product = $this->supplierProduct(stock: 1);

        $this->postJson('/api/v1/b2b/buy?api_key='.self::API_KEY, [
            'idempotency_key' => '019f906d-a49e-7e91-9331-589e9942bf26',
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertUnauthorized()
            ->assertJson(['message' => 'Unauthorized. API Key is missing.']);

        $this->assertDatabaseCount('orders', 0);
    }

    private function supplierProduct(int $stock): ProductModel
    {
        return ProductModel::create([
            'supplier_id' => $this->supplier->id,
            'external_id' => 'b2b-product',
            'category_id' => $this->category->id,
            'name' => 'B2B Supplier Product',
            'slug' => 'b2b-supplier-product',
            'price' => 0.02,
            'supplier_price_vnd' => 270,
            'supplier_stock' => $stock,
            'status' => 'active',
            'attributes' => [],
        ]);
    }

    private function fakeSuccessfulSupplierOrder(): void
    {
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::response([
                'data' => ['id' => 'B2B-SUPPLIER-ORDER-1'],
            ]),
            'https://ads7979.com/api/v2/order/detail' => Http::response([
                'data' => [
                    'id' => 'B2B-SUPPLIER-ORDER-1',
                    'accounts' => ['supplier-account-1'],
                ],
            ]),
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26000],
            ]),
        ]);
    }

    private function withApiKey()
    {
        return $this->withHeader('X-API-KEY', self::API_KEY);
    }
}
