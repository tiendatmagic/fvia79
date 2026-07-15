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

    public function getAllActiveProducts(): array
    {
        return $this->productRepository->findAllActive();
    }

    public function getActiveProductsPaginated(int $page, int $limit): array
    {
        return $this->productRepository->findAllActivePaginated($page, $limit);
    }

    public function getProductById(string $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(string $name, ?string $slug, ?string $description, float $price, string $status = 'active'): Product
    {
        $finalSlug = $slug ?: Str::slug($name);
        $existing = $this->productRepository->findBySlug($finalSlug);
        if ($existing) {
            $finalSlug .= '-' . time();
        }

        $product = new Product(null, $name, $finalSlug, $description, $price, $status);
        return $this->productRepository->save($product);
    }

    public function updateProduct(string $id, string $name, ?string $slug, ?string $description, float $price, string $status): Product
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \Exception('Product not found.');
        }

        $finalSlug = $slug ?: Str::slug($name);
        if ($finalSlug !== $product->getSlug()) {
            $existing = $this->productRepository->findBySlug($finalSlug);
            if ($existing) {
                $finalSlug .= '-' . time();
            }
        }

        $updatedProduct = new Product($id, $name, $finalSlug, $description, $price, $status);
        return $this->productRepository->save($updatedProduct);
    }

    public function deleteProduct(string $id): bool
    {
        return $this->productRepository->delete($id);
    }
}
