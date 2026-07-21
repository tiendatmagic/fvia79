<?php

namespace Tests\Feature;

use App\Application\Services\SupplierCatalogSyncService;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupplierCatalogSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_and_updates_ads7979_catalog_without_duplicates(): void
    {
        $this->fakeSupplierAndRateApis();
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update([
            'price_adjustment_type' => 'fixed',
            'price_adjustment_value' => 20000,
        ]);
        $service = app(SupplierCatalogSyncService::class);
        $first = $service->sync($supplier);
        $second = $service->sync($supplier->fresh());

        $this->assertSame(['categories' => 1, 'products' => 1], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, CategoryModel::where('supplier_id', $supplier->id)->count());
        $this->assertSame(1, ProductModel::where('supplier_id', $supplier->id)->count());
        $product = ProductModel::where('external_id', '2')->firstOrFail();
        $this->assertSame(20000.0, $product->supplier_price_vnd);
        $this->assertSame(1.54, $product->price);
        $this->assertSame(17, $product->supplier_stock);
        $this->assertSame([], $product->attributes);
        $this->assertSame('success', $supplier->fresh()->last_sync_status);
        Http::assertSentCount(3);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.coingecko.com/api/v3/simple/price'));
    }

    public function test_percent_adjustment_is_converted_to_usd(): void
    {
        $this->fakeSupplierAndRateApis();
        $supplier = SupplierModel::where('code', 'ads7979')->firstOrFail();
        $supplier->update([
            'price_adjustment_type' => 'percent',
            'price_adjustment_value' => 25,
        ]);
        app(SupplierCatalogSyncService::class)->sync($supplier);

        $this->assertSame(0.96, ProductModel::where('external_id', '2')->value('price'));
    }

    public function test_supplier_stock_is_returned_as_available_count(): void
    {
        $this->fakeSupplierAndRateApis();
        app(SupplierCatalogSyncService::class)->syncByCode('ads7979');

        $response = $this->getJson('/api/v1/products?status=active');

        $response->assertOk()
            ->assertJsonPath('0.available_count', 17)
            ->assertJsonPath('0.supplier.code', 'ads7979')
            ->assertJsonPath('0.supplier.external_id', '2');
    }

    private function fakeSupplierAndRateApis(): void
    {
        Cache::forget('usdt_to_vnd_rate');
        config()->set('services.ads7979.api_key', 'test-api-key');
        Http::fake([
            'https://ads7979.com/api/v2/categories' => Http::response($this->catalog()),
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'tether' => ['vnd' => 26000],
            ]),
        ]);
    }

    private function catalog(): array
    {
        return [[
            'id' => 1,
            'name' => 'BM50$',
            'image' => '/images/stores/BM_Old.jpg',
            'children' => [[
                'id' => 2,
                'name' => 'BM50$ Ngâm',
                'image' => '/images/stores/BM_Old.jpg',
                'quantity' => 17,
                'price' => 20000,
                'export_format' => ['uid', 'password'],
                'data' => [
                    'min_purchase' => '1',
                    'warranty_days' => '1',
                    'warranty' => 'Test warranty',
                    'description' => 'Test description',
                    'hdsd' => 'Test instructions',
                ],
            ]],
        ]];
    }
}
