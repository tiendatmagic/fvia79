<?php

namespace Tests\Feature;

use App\Application\Services\OrderService;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Infrastructure\Persistence\Eloquent\SupplierPurchaseModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupplierOrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $customer;

    private SupplierModel $supplier;

    private CategoryModel $category;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('usdt_to_vnd_rate');
        $this->customer = UserModel::create([
            'username' => 'supplier_checkout_customer',
            'name' => 'Supplier Checkout Customer',
            'email' => 'supplier-checkout@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 100,
            'status' => 'active',
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
            'external_id' => '10',
            'name' => 'Hotmail',
            'slug' => 'supplier-hotmail',
        ]);
    }

    public function test_supplier_inventory_is_bought_before_local_inventory(): void
    {
        $product = $this->supplierProduct(stock: 5);
        $this->addLocalStock($product, ['local-1', 'local-2']);
        $this->fakeRateAndSupplierOrder(['supplier-1', 'supplier-2'], 'SUP-1001');

        $response = $this->checkout($product, 2, '019f906d-a49e-7e91-9331-589e9942bf01');

        $response->assertCreated()->assertJsonPath('order.status', 'paid');
        $item = OrderItemModel::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(2, $item->supplier_quantity);
        $this->assertSame(0, $item->local_quantity);
        $this->assertSame('SUP-1001', $item->supplier_order_id);
        $this->assertSame(['supplier-1', 'supplier-2'], $item->delivered_keys);
        $this->assertSame(0, InventoryBatchModel::where('product_id', $product->id)->value('sold_count'));
        $this->assertSame(3, $product->fresh()->supplier_stock);
        $this->assertDatabaseHas('supplier_purchases', [
            'order_id' => $response->json('order.id'),
            'supplier_id' => $this->supplier->id,
            'supplier_order_id' => 'SUP-1001',
            'quantity' => 2,
            'status' => 'succeeded',
        ]);
        $this->assertStringNotContainsString(
            'supplier-1',
            DB::table('supplier_purchases')->value('response_payload'),
        );
        $this->withToken($this->customerToken())
            ->getJson('/api/v1/orders/'.$response->json('order.id'))
            ->assertOk()
            ->assertJsonPath('items.0.supplier_quantity', 2)
            ->assertJsonPath('items.0.local_quantity', 0)
            ->assertJsonPath('items.0.supplier_order_id', 'SUP-1001')
            ->assertJsonPath('accounts.0.data', 'supplier-1');
    }

    public function test_supplier_stock_and_local_stock_are_combined_for_partial_fulfillment(): void
    {
        $product = $this->supplierProduct(stock: 2);
        $this->addLocalStock($product, ['local-1', 'local-2']);
        $this->fakeRateAndSupplierOrder(['supplier-1', 'supplier-2'], 'SUP-1002');

        $this->getJson('/api/v1/products?status=active')
            ->assertOk()
            ->assertJsonPath('0.available_count', 4);

        $this->checkout($product, 3, '019f906d-a49e-7e91-9331-589e9942bf02')
            ->assertCreated()
            ->assertJsonPath('order.status', 'paid');

        $item = OrderItemModel::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(2, $item->supplier_quantity);
        $this->assertSame(1, $item->local_quantity);
        $this->assertSame(['supplier-1', 'supplier-2', 'local-1'], $item->delivered_keys);
        $this->assertSame(1, InventoryBatchModel::where('product_id', $product->id)->value('sold_count'));
        $this->assertSame(0, $product->fresh()->supplier_stock);
    }

    public function test_supplier_out_of_stock_falls_back_to_local_inventory(): void
    {
        $product = $this->supplierProduct(stock: 2);
        $this->addLocalStock($product, ['local-1', 'local-2']);
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::response([
                'message' => 'Danh mục này đã hết',
                'code' => 400,
            ], 400),
        ]);

        $this->checkout($product, 2, '019f906d-a49e-7e91-9331-589e9942bf03')
            ->assertCreated()
            ->assertJsonPath('order.status', 'paid');

        $item = OrderItemModel::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(0, $item->supplier_quantity);
        $this->assertSame(2, $item->local_quantity);
        $this->assertSame(['local-1', 'local-2'], $item->delivered_keys);
        $this->assertSame(0, $product->fresh()->supplier_stock);
        $this->assertSame('failed', SupplierPurchaseModel::firstOrFail()->status);
    }

    public function test_checkout_idempotency_key_does_not_purchase_twice(): void
    {
        $product = $this->supplierProduct(stock: 2);
        $this->fakeRateAndSupplierOrder(['supplier-1'], 'SUP-1003');
        $key = '019f906d-a49e-7e91-9331-589e9942bf04';

        $first = $this->checkout($product, 1, $key)->assertCreated();
        $second = $this->checkout($product, 1, $key)->assertCreated();

        $this->assertSame($first->json('order.id'), $second->json('order.id'));
        $this->assertSame(1, SupplierPurchaseModel::count());
        Http::assertSentCount(3);
    }

    public function test_failed_supplier_and_missing_local_stock_cancel_and_refund_order(): void
    {
        $product = $this->supplierProduct(stock: 2);
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::response([
                'message' => 'Danh mục này đã hết',
                'code' => 400,
            ], 400),
        ]);
        $balanceBefore = $this->customer->balance;

        $this->checkout($product, 2, '019f906d-a49e-7e91-9331-589e9942bf05')
            ->assertBadRequest();

        $this->assertSame($balanceBefore, $this->customer->fresh()->balance);
        $this->assertDatabaseHas('orders', ['status' => 'cancelled']);
        $this->assertSame('failed', SupplierPurchaseModel::firstOrFail()->status);
    }

    public function test_uncertain_supplier_response_stays_pending_and_is_not_retried(): void
    {
        $product = $this->supplierProduct(stock: 2);
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::failedConnection(),
        ]);
        $balanceBefore = $this->customer->balance;

        $this->checkout($product, 1, '019f906d-a49e-7e91-9331-589e9942bf06')
            ->assertAccepted()
            ->assertJsonPath('message', 'Order is pending supplier reconciliation.')
            ->assertJsonPath('order.status', 'pending');

        $this->assertLessThan($balanceBefore, $this->customer->fresh()->balance);
        $this->assertDatabaseHas('orders', ['status' => 'pending']);
        $this->assertSame('unknown', SupplierPurchaseModel::firstOrFail()->status);
        Http::assertSentCount(1);
    }

    public function test_pending_supplier_order_is_reconciled_from_order_history_without_repurchase(): void
    {
        $product = $this->supplierProduct(stock: 1);
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::response([
                'data' => ['id' => 'SUP-PENDING-1'],
            ]),
            'https://ads7979.com/api/v2/order/detail' => Http::sequence()
                ->push(['message' => 'Order is processing'], 503)
                ->push(['message' => 'Order is processing'], 503)
                ->push(['message' => 'Order is processing'], 503)
                ->push([
                    'data' => [
                        'id' => 'SUP-PENDING-1',
                        'accounts' => ['supplier-delayed-account'],
                    ],
                ]),
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26000],
            ]),
        ]);

        $response = $this->checkout($product, 1, '019f906d-a49e-7e91-9331-589e9942bf07')
            ->assertAccepted()
            ->assertJsonPath('order.status', 'pending');
        $this->assertDatabaseHas('supplier_purchases', [
            'order_id' => $response->json('order.id'),
            'supplier_order_id' => 'SUP-PENDING-1',
            'status' => 'unknown',
        ]);

        $results = app(OrderService::class)->reconcilePendingSupplierOrders();

        $this->assertSame('paid', $results[$response->json('order.id')]);
        $this->assertDatabaseHas('orders', [
            'id' => $response->json('order.id'),
            'status' => 'paid',
        ]);
        $this->assertSame(
            ['supplier-delayed-account'],
            OrderItemModel::where('order_id', $response->json('order.id'))->firstOrFail()->delivered_keys,
        );
        Http::assertSentCount(6);
        $this->assertSame(1, collect(Http::recorded())->filter(
            fn (array $record): bool => $record[0]->url() === 'https://ads7979.com/api/v2/order/create',
        )->count());
    }

    public function test_deterministic_supplier_rejection_cancels_refunds_and_restores_reservation(): void
    {
        $product = $this->supplierProduct(stock: 2);
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::response([
                'message' => 'Số dư tài khoản không đủ',
                'code' => 400,
            ], 400),
        ]);
        $balanceBefore = $this->customer->balance;

        $this->checkout($product, 1, '019f906d-a49e-7e91-9331-589e9942bf08')
            ->assertBadRequest()
            ->assertJsonPath(
                'message',
                'The supplier rejected the purchase. The order has been cancelled and refunded.',
            );

        $this->assertSame($balanceBefore, $this->customer->fresh()->balance);
        $this->assertSame(2, $product->fresh()->supplier_stock);
        $this->assertDatabaseHas('orders', ['status' => 'cancelled']);
        $this->assertSame('failed', SupplierPurchaseModel::firstOrFail()->status);
    }

    private function supplierProduct(int $stock): ProductModel
    {
        return ProductModel::create([
            'supplier_id' => $this->supplier->id,
            'external_id' => '94',
            'category_id' => $this->category->id,
            'name' => 'Hotmail Trusted [GRAPH API]',
            'slug' => 'ads7979-94-hotmail-trusted',
            'price' => 0.02,
            'supplier_price_vnd' => 270,
            'supplier_stock' => $stock,
            'status' => 'active',
            'attributes' => [],
        ]);
    }

    /**
     * @param  array<int, string>  $resources
     */
    private function addLocalStock(ProductModel $product, array $resources): void
    {
        $batch = InventoryBatchModel::create([
            'product_id' => $product->id,
            'batch_name' => 'Local fallback',
            'cost_price' => 0.01,
            'initial_count' => count($resources),
            'sold_count' => 0,
            'status' => 'active',
        ]);
        foreach ($resources as $resource) {
            InventoryItemModel::create([
                'batch_id' => $batch->id,
                'product_id' => $product->id,
                'secret_data' => $resource,
                'status' => 'available',
            ]);
        }
    }

    /**
     * @param  array<int, string>  $resources
     */
    private function fakeRateAndSupplierOrder(array $resources, string $orderId): void
    {
        Http::fake([
            'https://ads7979.com/api/v2/order/create' => Http::response([
                'data' => ['id' => $orderId],
            ]),
            'https://ads7979.com/api/v2/order/detail' => Http::response([
                'data' => [
                    'id' => $orderId,
                    'accounts' => $resources,
                ],
            ]),
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26000],
            ]),
        ]);
    }

    private function checkout(ProductModel $product, int $quantity, string $idempotencyKey)
    {
        return $this->withToken($this->customerToken())->postJson('/api/v1/orders', [
            'idempotency_key' => $idempotencyKey,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]],
        ]);
    }

    private function customerToken(): string
    {
        return app(JwtService::class)->generateToken([
            'sub' => $this->customer->id,
            'email' => $this->customer->email,
            'role' => 'customer',
            'ver' => 0,
        ], 1800);
    }
}
