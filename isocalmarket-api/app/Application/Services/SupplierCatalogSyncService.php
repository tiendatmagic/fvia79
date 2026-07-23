<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\SupplierConnectorFactory;
use App\Integrations\Suppliers\SupplierOperationLock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SupplierCatalogSyncService
{
    public function __construct(
        private readonly SupplierConnectorFactory $connectorFactory,
        private readonly SupplierPriceService $priceService,
        private readonly SupplierOperationLock $operationLock,
    ) {}

    public function syncByCode(string $code): array
    {
        $supplier = SupplierModel::where('code', $code)->firstOrFail();

        return $this->sync($supplier);
    }

    public function sync(SupplierModel $supplier): array
    {
        return $this->operationLock->run($supplier, function () use ($supplier): array {
            $supplier->refresh();

            return $this->syncUnlocked($supplier);
        });
    }

    private function syncUnlocked(SupplierModel $supplier): array
    {
        if (! $supplier->enabled) {
            throw new RuntimeException("Supplier {$supplier->name} is disabled.");
        }

        try {
            $catalog = $this->connectorFactory->make($supplier)->fetchCatalog($supplier);
            $result = DB::transaction(fn () => $this->persistCatalog($supplier, $catalog));
            $this->markSucceeded($supplier);
            $this->clearStorefrontCache();

            return $result;
        } catch (Throwable $exception) {
            $this->markFailed($supplier, $exception);
            throw $exception;
        }
    }

    public function syncDue(): array
    {
        $results = [];
        $suppliers = SupplierModel::where('enabled', true)->where('auto_sync', true)->get();

        foreach ($suppliers as $supplier) {
            if ($this->isDue($supplier)) {
                try {
                    $results[$supplier->code] = $this->sync($supplier);
                } catch (Throwable) {
                    continue;
                }
            }
        }

        return $results;
    }

    private function persistCatalog(SupplierModel $supplier, array $catalog): array
    {
        $categoryCount = 0;
        $productCount = 0;
        $externalProductIds = [];

        foreach ($catalog as $categoryData) {
            $category = $this->upsertCategory($supplier, $categoryData);
            $categoryCount++;

            foreach (($categoryData['children'] ?? []) as $productData) {
                $externalId = (string) ($productData['id'] ?? '');
                if ($externalId === '') {
                    throw new RuntimeException('Supplier product is missing its id.');
                }

                $externalProductIds[] = $externalId;
                $stock = max(0, (int) ($productData['quantity'] ?? 0));
                if (! $supplier->import_out_of_stock && $stock === 0) {
                    $this->hideExistingOutOfStockProduct($supplier, $externalId);

                    continue;
                }

                $this->upsertProduct($supplier, $category, $productData, $stock);
                $productCount++;
            }
        }

        $this->deactivateMissingProducts($supplier, $externalProductIds);

        return ['categories' => $categoryCount, 'products' => $productCount];
    }

    private function upsertCategory(SupplierModel $supplier, array $data): CategoryModel
    {
        $externalId = (string) ($data['id'] ?? '');
        if ($externalId === '') {
            throw new RuntimeException('Supplier category is missing its id.');
        }

        return CategoryModel::updateOrCreate(
            ['supplier_id' => $supplier->id, 'external_id' => $externalId],
            [
                'name' => (string) ($data['name'] ?? "Category {$externalId}"),
                'slug' => $this->sourceSlug($supplier, $externalId, (string) ($data['name'] ?? 'category')),
                'description' => null,
            ],
        );
    }

    private function upsertProduct(
        SupplierModel $supplier,
        CategoryModel $category,
        array $data,
        int $stock,
    ): void {
        $externalId = (string) ($data['id'] ?? '');
        if ($externalId === '') {
            throw new RuntimeException('Supplier product is missing its id.');
        }

        $product = ProductModel::firstOrNew([
            'supplier_id' => $supplier->id,
            'external_id' => $externalId,
        ]);
        $priceVnd = (float) ($data['price'] ?? 0);
        $product->fill([
            'category_id' => $category->id,
            'name' => (string) ($data['name'] ?? "Product {$externalId}"),
            'slug' => $this->sourceSlug($supplier, $externalId, (string) ($data['name'] ?? 'product')),
            'description' => $data['data']['description'] ?? null,
            'price' => $this->priceService->calculateUsd($priceVnd, $supplier),
            'supplier_price_vnd' => $priceVnd,
            'supplier_stock' => $stock,
            'supplier_synced_at' => now(),
            'attributes' => [],
        ]);

        if ($product->exists && $product->supplier_out_of_stock_hidden) {
            $product->supplier_out_of_stock_hidden = false;
            if ($supplier->products_enabled) {
                $product->status = 'active';
            }
        }

        if (! $supplier->products_enabled) {
            if ($product->supplier_previous_status === null) {
                $product->supplier_previous_status = 'active';
            }
            $product->status = 'inactive';
        } elseif (! $product->exists) {
            $product->status = 'active';
        }
        $product->save();
    }

    private function hideExistingOutOfStockProduct(SupplierModel $supplier, string $externalId): void
    {
        $product = ProductModel::where('supplier_id', $supplier->id)
            ->where('external_id', $externalId)
            ->first();

        if (! $product) {
            return;
        }

        $product->supplier_stock = 0;
        $product->supplier_synced_at = now();
        if ($product->status === 'active') {
            $product->status = 'inactive';
            $product->supplier_out_of_stock_hidden = true;
        }
        $product->save();
    }

    private function sourceSlug(SupplierModel $supplier, string $externalId, string $name): string
    {
        return Str::limit(Str::slug("{$supplier->code}-{$externalId}-{$name}"), 240, '');
    }

    private function deactivateMissingProducts(SupplierModel $supplier, array $externalIds): void
    {
        $query = ProductModel::where('supplier_id', $supplier->id);
        if ($externalIds !== []) {
            $query->whereNotIn('external_id', $externalIds);
        }
        $query->update([
            'status' => 'inactive',
            'supplier_previous_status' => null,
            'supplier_stock' => 0,
            'supplier_out_of_stock_hidden' => false,
        ]);
    }

    private function isDue(SupplierModel $supplier): bool
    {
        return ! $supplier->last_synced_at
            || $supplier->last_synced_at->lte(now()->subMinutes($supplier->sync_interval_minutes));
    }

    private function markSucceeded(SupplierModel $supplier): void
    {
        $supplier->update([
            'last_synced_at' => now(),
            'last_sync_status' => 'success',
            'last_sync_error' => null,
        ]);
    }

    private function markFailed(SupplierModel $supplier, Throwable $exception): void
    {
        $supplier->update([
            'last_sync_status' => 'failed',
            'last_sync_error' => Str::limit($exception->getMessage(), 2000),
        ]);
    }

    private function clearStorefrontCache(): void
    {
        Cache::forget('products_active_storefront');
        Cache::forget('categories_active_storefront');
    }
}
