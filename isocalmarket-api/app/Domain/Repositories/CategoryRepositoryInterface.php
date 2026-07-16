<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    /**
     * @return Category[]
     */
    public function findAll(): array;
    public function findById(string $id): ?Category;
    public function findBySlug(string $slug): ?Category;
    public function save(Category $category): Category;
    public function delete(string $id): bool;
}
