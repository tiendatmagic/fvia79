<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    /**
     * @return Category[]
     */
    public function findAll(?string $search = null): array;
    public function findAllPaginated(int $page, int $limit, ?string $search = null): array;
    public function findById(string $id): ?Category;
    public function findBySlug(string $slug): ?Category;
    public function save(Category $category): Category;
    public function delete(string $id): bool;
}
