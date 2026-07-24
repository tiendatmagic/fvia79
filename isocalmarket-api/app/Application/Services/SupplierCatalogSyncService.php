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
        private readonly GoogleTranslateService $translator,
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
            $this->warmTranslationCache($supplier, $catalog);
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

    private function warmTranslationCache(SupplierModel $supplier, array $catalog): void
    {
        if (! $supplier->auto_translate) {
            return;
        }

        $existingCategories = CategoryModel::where('supplier_id', $supplier->id)
            ->get(['external_id', 'name_vi', 'attributes'])
            ->keyBy(fn (CategoryModel $category) => (string) $category->external_id);
        $existingProducts = ProductModel::where('supplier_id', $supplier->id)
            ->get(['external_id', 'name_vi', 'description_vi', 'attributes'])
            ->keyBy(fn (ProductModel $product) => (string) $product->external_id);
        $texts = [];

        foreach ($catalog as $categoryData) {
            $categoryId = (string) ($categoryData['id'] ?? '');
            if ($categoryId !== '') {
                $rawName = (string) ($categoryData['name'] ?? "Category {$categoryId}");
                $existing = $existingCategories->get($categoryId);
                $savedRawName = data_get($existing?->attributes, 'raw_name');

                if (! $existing || $savedRawName !== $rawName || empty($existing->name_vi)) {
                    $texts[] = $rawName;
                }
            }

            foreach (($categoryData['children'] ?? []) as $productData) {
                $productId = (string) ($productData['id'] ?? '');
                if ($productId === '') {
                    continue;
                }

                $stock = max(0, (int) ($productData['quantity'] ?? 0));
                if (! $supplier->import_out_of_stock && $stock === 0) {
                    continue;
                }

                $rawName = (string) ($productData['name'] ?? "Product {$productId}");
                $rawDescription = $this->normalizeDescription($productData['data']['description'] ?? null);
                $existing = $existingProducts->get($productId);
                $savedRawName = data_get($existing?->attributes, 'raw_name');
                $savedRawDescription = data_get($existing?->attributes, 'raw_desc');

                if (! $existing || $savedRawName !== $rawName || empty($existing->name_vi)) {
                    $texts[] = $rawName;
                }
                if (
                    $rawDescription !== null
                    && $rawDescription !== ''
                    && (! $existing
                        || $savedRawDescription !== $rawDescription
                        || empty($existing->description_vi))
                ) {
                    $texts[] = $rawDescription;
                }
            }
        }

        $this->translator->translateMany($texts, 'vi');
    }

    private function upsertCategory(SupplierModel $supplier, array $data): CategoryModel
    {
        $externalId = (string) ($data['id'] ?? '');
        if ($externalId === '') {
            throw new RuntimeException('Supplier category is missing its id.');
        }

        $rawName = (string) ($data['name'] ?? "Category {$externalId}");
        $category = CategoryModel::firstOrNew([
            'supplier_id' => $supplier->id,
            'external_id' => $externalId,
        ]);

        $attributes = $category->attributes ?? [];
        $savedRawName = $attributes['raw_name'] ?? null;

        $nameEn = $rawName;
        $nameVi = $rawName;

        if ($supplier->auto_translate) {
            $nameVi = $category->name_vi;
            if (! $category->exists || $savedRawName !== $rawName || empty($nameVi)) {
                $nameVi = $this->translator->translate($rawName, 'vi') ?? $rawName;
            }
        }

        $attributes['raw_name'] = $rawName;

        $category->fill([
            'name' => $rawName,
            'name_en' => $nameEn,
            'name_vi' => $nameVi,
            'slug' => $this->sourceSlug($supplier, $externalId, $rawName),
            'description' => null,
            'attributes' => $attributes,
        ]);
        $category->save();

        return $category;
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

        $rawName = (string) ($data['name'] ?? "Product {$externalId}");
        $rawDesc = $this->normalizeDescription($data['data']['description'] ?? null);

        $attributes = $product->attributes ?? [];
        $savedRawName = $attributes['raw_name'] ?? null;
        $savedRawDesc = $attributes['raw_desc'] ?? null;

        $nameEn = $rawName;
        $nameVi = $rawName;

        if ($supplier->auto_translate) {
            $nameVi = $product->name_vi;
            if (! $product->exists || $savedRawName !== $rawName || empty($nameVi)) {
                $nameVi = $this->translator->translate($rawName, 'vi') ?? $rawName;
            }
        }

        $descEn = $rawDesc;
        $descVi = $rawDesc;

        if ($supplier->auto_translate) {
            $descVi = $product->description_vi;
            if (! $product->exists || $savedRawDesc !== $rawDesc || ($rawDesc !== null && empty($descVi))) {
                if ($rawDesc !== null && trim($rawDesc) !== '') {
                    $descVi = $this->translator->translate($rawDesc, 'vi') ?? $rawDesc;
                } else {
                    $descVi = null;
                }
            }
        }

        $attributes['raw_name'] = $rawName;
        $attributes['raw_desc'] = $rawDesc;

        $product->fill([
            'category_id' => $category->id,
            'name' => $rawName,
            'name_en' => $nameEn,
            'name_vi' => $nameVi,
            'slug' => $this->sourceSlug($supplier, $externalId, $rawName),
            'description' => $rawDesc,
            'description_en' => $descEn,
            'description_vi' => $descVi,
            'price' => $this->priceService->calculateUsd($priceVnd, $supplier),
            'supplier_price_vnd' => $priceVnd,
            'supplier_stock' => $stock,
            'supplier_synced_at' => now(),
            'attributes' => $attributes,
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

        if ($product->status === 'active' && $stock > 0 && ! $category->is_active) {
            $category->is_active = true;
            $category->save();
        }
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

    private function normalizeDescription(mixed $description): ?string
    {
        if ($description === null) {
            return null;
        }

        return is_scalar($description) ? trim((string) $description) : null;
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
        Cache::forget('categories_active_storefront_en');
        Cache::forget('categories_active_storefront_vi');
    }
}
