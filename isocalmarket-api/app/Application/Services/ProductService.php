<?php

namespace App\Application\Services;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
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
        return $this->productRepository->findAllActive($categoryId, $status, $search, $filterDeleted);
    }

    public function getActiveProductsPaginated(int $page, int $limit, ?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active'): array
    {
        return $this->productRepository->findAllActivePaginated($page, $limit, $categoryId, $status, $search, $filterDeleted);
    }

    public function getProductById(string $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(string $name, ?string $slug, ?string $description, float $price, string $status = 'active', ?array $attributes = null, ?string $categoryId = null): Product
    {
        $finalSlug = $slug ?: Str::slug($name);
        $existing = $this->productRepository->findBySlug($finalSlug);
        if ($existing) {
            $finalSlug .= '-' . time();
        }

        $product = new Product(null, $name, $finalSlug, $description, $price, $status, 0, $attributes, $categoryId);
        return $this->productRepository->save($product);
    }

    public function updateProduct(string $id, string $name, ?string $slug, ?string $description, float $price, string $status, ?array $attributes = null, ?string $categoryId = null): Product
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \Exception('Product not found.');
        }

        if ($product->isDeleted()) {
            throw new \Exception('Cannot update a soft-deleted product.');
        }

        $finalSlug = $slug ?: Str::slug($name);
        if ($finalSlug !== $product->getSlug()) {
            $existing = $this->productRepository->findBySlug($finalSlug);
            if ($existing) {
                $finalSlug .= '-' . time();
            }
        }

        $updatedProduct = new Product($id, $name, $finalSlug, $description, $price, $status, 0, $attributes, $categoryId);
        return $this->productRepository->save($updatedProduct);
    }

    public function deleteProduct(string $id): bool
    {
        return $this->productRepository->delete($id);
    }

    public function restoreProduct(string $id): bool
    {
        return $this->productRepository->restore($id);
    }
}
