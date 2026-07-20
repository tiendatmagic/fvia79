<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function findAllActive(?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active'): array;
    public function findAllActivePaginated(int $page, int $limit, ?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active'): array;
    public function findById(string $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function save(Product $product): Product;
    public function delete(string $id): bool;
    public function restore(string $id): bool;
    public function findGroupedActiveProducts(int $limit, ?string $search = null): array;
}
