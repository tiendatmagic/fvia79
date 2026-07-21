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

    public function getAllActiveProducts(?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active'): array
    {
        $isStorefrontLoad = ! $categoryId && ($status === 'active' || ! $status) && ! $search && $filterDeleted === 'active';
        if ($isStorefrontLoad) {
            $cached = Cache::remember('products_active_storefront', 600, function () use ($categoryId, $status, $search, $filterDeleted) {
                $products = $this->productRepository->findAllActive($categoryId, $status, $search, $filterDeleted);

                return array_map(fn ($p) => [
                    'id' => $p->getId(),
                    'name' => $p->getName(),
                    'slug' => $p->getSlug(),
                    'description' => $p->getDescription(),
                    'price' => $p->getPrice(),
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
                $p['supplierSyncedAt'] ? new \DateTime($p['supplierSyncedAt']) : null
            ), $cached);
        }

        return $this->productRepository->findAllActive($categoryId, $status, $search, $filterDeleted);
    }

    public function getActiveProductsPaginated(int $page, int $limit, ?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active'): array
    {
        return $this->productRepository->findAllActivePaginated($page, $limit, $categoryId, $status, $search, $filterDeleted);
    }

    public function getGroupedActiveProducts(int $limit, ?string $search = null): array
    {
        return $this->productRepository->findGroupedActiveProducts($limit, $search);
    }

    public function getProductById(string $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(string $name, ?string $slug, ?string $description, float $price, string $status = 'active', ?array $attributes = null, ?string $categoryId = null): Product
    {
        $baseSlug = $slug ?: Str::slug($name);
        $finalSlug = $this->generateUniqueSlug($baseSlug);

        $product = new Product(null, $name, $finalSlug, $description, $price, $status, 0, $attributes, $categoryId);
        $saved = $this->productRepository->save($product);
        Cache::forget('products_active_storefront');
        Cache::forget('categories_active_storefront');

        return $saved;
    }

    public function updateProduct(string $id, string $name, ?string $slug, ?string $description, float $price, string $status, ?array $attributes = null, ?string $categoryId = null): Product
    {
        $product = $this->productRepository->findById($id);
        if (! $product) {
            throw new \Exception('Product not found.');
        }

        if ($product->isDeleted()) {
            throw new \Exception('Cannot update a soft-deleted product.');
        }

        $baseSlug = $slug ?: Str::slug($name);
        $finalSlug = $this->generateUniqueSlug($baseSlug, $id);

        $updatedProduct = new Product(
            $id, $name, $finalSlug, $description, $price, $status, $product->getAvailableCount(),
            $attributes, $categoryId, $product->getDeletedAt(), $product->getSupplierId(),
            $product->getSupplierCode(), $product->getSupplierName(), $product->getExternalId(),
            $product->getSupplierPriceVnd(), $product->getSupplierStock(), $product->getSupplierSyncedAt()
        );
        $saved = $this->productRepository->save($updatedProduct);
        Cache::forget('products_active_storefront');
        Cache::forget('categories_active_storefront');

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
            Cache::forget('products_active_storefront');
            Cache::forget('categories_active_storefront');
        }

        return $deleted;
    }

    public function restoreProduct(string $id): bool
    {
        $restored = $this->productRepository->restore($id);
        if ($restored) {
            Cache::forget('products_active_storefront');
            Cache::forget('categories_active_storefront');
        }

        return $restored;
    }
}
