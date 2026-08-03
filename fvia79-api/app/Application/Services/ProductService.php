<?php

namespace App\Application\Services;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllActiveProducts(?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active', ?int $maxResults = null): array
    {
        $isStorefrontLoad = ! $categoryId && ($status === 'active' || ! $status) && ! $search && $filterDeleted === 'active';
        if ($isStorefrontLoad) {
            $cacheKey = 'products_active_storefront'.($maxResults === null ? '_all' : '_limit_'.$maxResults);
            $cached = Cache::remember($cacheKey, 600, function () use ($categoryId, $status, $search, $filterDeleted, $maxResults) {
                $products = $this->productRepository->findAllActive($categoryId, $status, $search, $filterDeleted, $maxResults);

                return array_map(fn ($p) => [
                    'id' => $p->getId(),
                    'name' => $p->getName(),
                    'nameVi' => $p->getNameVi(),
                    'nameEn' => $p->getNameEn(),
                    'slug' => $p->getSlug(),
                    'description' => $p->getDescription(),
                    'descriptionVi' => $p->getDescriptionVi(),
                    'descriptionEn' => $p->getDescriptionEn(),
                    'price' => $p->getPrice(),
                    'isManualPrice' => $p->getIsManualPrice(),
                    'manualPrice' => $p->getManualPrice(),
                    'manualPriceCurrency' => $p->getManualPriceCurrency(),
                    'status' => $p->getStatus(),
                    'available_count' => $p->getAvailableCount(),
                    'attributes' => $p->getAttributes(),
                    'categoryId' => $p->getCategoryId(),
                    'deletedAt' => $p->getDeletedAt()?->format('Y-m-d H:i:s'),
                    'supplierId' => $p->getSupplierId(),
                    'supplierCode' => $p->getSupplierCode(),
                    'supplierName' => $p->getSupplierName(),
                    'externalId' => $p->getExternalId(),
                    'supplierPriceVnd' => $p->getSupplierPriceVnd(),
                    'supplierStock' => $p->getSupplierStock(),
                    'supplierSyncedAt' => $p->getSupplierSyncedAt()?->format('Y-m-d H:i:s'),
                ], $products);
            });

            return array_map(fn ($p) => new Product(
                $p['id'],
                $p['name'],
                $p['slug'],
                $p['description'],
                $p['price'],
                $p['status'],
                $p['available_count'],
                $p['attributes'],
                $p['categoryId'],
                $p['deletedAt'] ? new \DateTime($p['deletedAt']) : null,
                $p['supplierId'],
                $p['supplierCode'],
                $p['supplierName'],
                $p['externalId'],
                $p['supplierPriceVnd'],
                $p['supplierStock'],
                $p['supplierSyncedAt'] ? new \DateTime($p['supplierSyncedAt']) : null,
                $p['nameVi'] ?? null,
                $p['nameEn'] ?? null,
                $p['descriptionVi'] ?? null,
                $p['descriptionEn'] ?? null,
                $p['isManualPrice'] ?? false,
                $p['manualPrice'] ?? null,
                $p['manualPriceCurrency'] ?? 'VND'
            ), $cached);
        }

        return $this->productRepository->findAllActive($categoryId, $status, $search, $filterDeleted, $maxResults);
    }

    public function getActiveProductsPaginated(int $page, int $limit, ?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active', bool $includeOutOfStock = false): array
    {
        return $this->productRepository->findAllActivePaginated($page, $limit, $categoryId, $status, $search, $filterDeleted, $includeOutOfStock);
    }

    public function getGroupedActiveProducts(int $limit, ?string $search = null, bool $includeOutOfStock = false): array
    {
        return $this->productRepository->findGroupedActiveProducts($limit, $search, $includeOutOfStock);
    }

    public function getProductById(string $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(
        string $name,
        ?string $slug,
        ?string $description,
        float $price,
        string $status = 'active',
        ?array $attributes = null,
        ?string $categoryId = null,
        ?string $nameVi = null,
        ?string $nameEn = null,
        ?string $descriptionVi = null,
        ?string $descriptionEn = null,
        bool $isManualPrice = false,
        ?float $manualPrice = null,
        ?string $manualPriceCurrency = 'VND',
    ): Product {
        $baseSlug = $slug ?: Str::slug($name);
        $finalSlug = $this->generateUniqueSlug($baseSlug);
        $currency = strtoupper($manualPriceCurrency ?: 'VND');

        if ($isManualPrice && $manualPrice !== null) {
            if ($currency === 'USDT' || $currency === 'USD') {
                $finalPrice = (float) $manualPrice;
            } else {
                $depositService = app(DepositService::class);
                $rate = $depositService->getUsdtToVndRate();
                $finalPrice = $rate > 0 ? round((float) $manualPrice / $rate, 8) : (float) $manualPrice;
            }
        } else {
            $finalPrice = $price;
        }

        $product = new Product(
            null, $name, $finalSlug, $description, $finalPrice, $status, 0, $attributes, $categoryId,
            null, null, null, null, null, null, null, null,
            $nameVi, $nameEn, $descriptionVi, $descriptionEn,
            $isManualPrice, $isManualPrice ? $manualPrice : null,
            $isManualPrice ? $currency : 'VND'
        );
        $saved = $this->productRepository->save($product);
        $this->forgetCatalogCaches();

        return $saved;
    }

    public function updateProduct(
        string $id,
        string $name,
        ?string $slug,
        ?string $description,
        float $price,
        string $status,
        ?array $attributes = null,
        ?string $categoryId = null,
        ?string $nameVi = null,
        ?string $nameEn = null,
        ?string $descriptionVi = null,
        ?string $descriptionEn = null,
        bool $isManualPrice = false,
        ?float $manualPrice = null,
        ?string $manualPriceCurrency = 'VND',
    ): Product {
        $product = $this->productRepository->findById($id);
        if (! $product) {
            throw new \Exception('Product not found.');
        }

        if ($product->isDeleted()) {
            throw new \Exception('Cannot update a soft-deleted product.');
        }

        $baseSlug = $slug ?: Str::slug($name);
        $finalSlug = $this->generateUniqueSlug($baseSlug, $id);
        $currency = strtoupper($manualPriceCurrency ?: 'VND');

        if ($isManualPrice && $manualPrice !== null) {
            if ($currency === 'USDT' || $currency === 'USD') {
                $finalPrice = (float) $manualPrice;
            } else {
                $depositService = app(DepositService::class);
                $rate = $depositService->getUsdtToVndRate();
                $finalPrice = $rate > 0 ? round((float) $manualPrice / $rate, 8) : (float) $manualPrice;
            }
        } else {
            $finalPrice = $price;
        }

        if (! $isManualPrice && $product->getIsManualPrice() && $product->getSupplierId() && $product->getSupplierPriceVnd() > 0) {
            try {
                $supplierModel = \App\Infrastructure\Persistence\Eloquent\SupplierModel::find($product->getSupplierId());
                if ($supplierModel) {
                    $priceService = app(SupplierPriceService::class);
                    $finalPrice = $priceService->calculateUsd($product->getSupplierPriceVnd(), $supplierModel);
                }
            } catch (\Throwable) {
            }
        }

        $updatedProduct = new Product(
            $id, $name, $finalSlug, $description, $finalPrice, $status, $product->getAvailableCount(),
            $attributes, $categoryId, $product->getDeletedAt(), $product->getSupplierId(),
            $product->getSupplierCode(), $product->getSupplierName(), $product->getExternalId(),
            $product->getSupplierPriceVnd(), $product->getSupplierStock(), $product->getSupplierSyncedAt(),
            $nameVi, $nameEn, $descriptionVi, $descriptionEn,
            $isManualPrice, $isManualPrice ? $manualPrice : null,
            $isManualPrice ? $currency : 'VND'
        );
        $saved = $this->productRepository->save($updatedProduct);
        $this->forgetCatalogCaches();

        return $saved;
    }

    private function generateUniqueSlug(string $baseSlug, ?string $excludeId = null): string
    {
        $slug = $baseSlug;
        $count = 1;
        while (true) {
            $existing = $this->productRepository->findBySlug($slug);
            if (! $existing || ($excludeId && $existing->getId() === $excludeId)) {
                return $slug;
            }
            $slug = $baseSlug.'-'.$count;
            $count++;
        }
    }

    public function deleteProduct(string $id): bool
    {
        $deleted = $this->productRepository->delete($id);
        if ($deleted) {
            $this->forgetCatalogCaches();
        }

        return $deleted;
    }

    public function restoreProduct(string $id): bool
    {
        $restored = $this->productRepository->restore($id);
        if ($restored) {
            $this->forgetCatalogCaches();
        }

        return $restored;
    }

    private function forgetCatalogCaches(): void
    {
        Cache::forget('products_active_storefront');
        Cache::forget('products_active_storefront_all');
        for ($limit = 1; $limit <= 100; $limit++) {
            Cache::forget('products_active_storefront_limit_'.$limit);
        }
        Cache::forget('categories_active_storefront');
        Cache::forget('categories_active_storefront_en');
        Cache::forget('categories_active_storefront_vi');
    }
}
