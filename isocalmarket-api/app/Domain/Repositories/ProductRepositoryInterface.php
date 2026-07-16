<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    /**
     * @return Product[]
     */
    public function findAllActive(?string $categoryId = null): array;
    public function findAllActivePaginated(int $page, int $limit, ?string $categoryId = null): array;
    public function findById(string $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function save(Product $product): Product;
    public function delete(string $id): bool;
}
