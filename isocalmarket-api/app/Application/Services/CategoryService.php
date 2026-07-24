<?php

namespace App\Application\Services;

use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use Illuminate\Support\Str;

class CategoryService
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function createCategory(string $name, ?string $slug, ?string $description, bool $isActive = true): Category
    {
        $baseSlug = $slug ? Str::slug($slug) : Str::slug($name);
        $finalSlug = $this->generateUniqueSlug($baseSlug);

        $category = new Category(null, $name, $finalSlug, $description, $isActive);
        return $this->categoryRepository->save($category);
    }

    public function updateCategory(string $id, string $name, ?string $slug, ?string $description, bool $isActive = true): Category
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \Exception('Category not found.');
        }

        $baseSlug = $slug ? Str::slug($slug) : Str::slug($name);
        $finalSlug = $this->generateUniqueSlug($baseSlug, $id);

        $updatedCategory = new Category($id, $name, $finalSlug, $description, $isActive);
        return $this->categoryRepository->save($updatedCategory);
    }

    private function generateUniqueSlug(string $baseSlug, ?string $excludeId = null): string
    {
        $slug = $baseSlug;
        $count = 1;
        while (true) {
            $existing = $this->categoryRepository->findBySlug($slug);
            if (!$existing || ($excludeId && $existing->getId() === $excludeId)) {
                return $slug;
            }
            $slug = $baseSlug . '-' . $count;
            $count++;
        }
    }

    public function deleteCategory(string $id): bool
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \Exception('Category not found.');
        }

        $hasProducts = ProductModel::withTrashed()->where('category_id', $id)->exists();
        if ($hasProducts) {
            throw new \Exception('Cannot delete category containing products.');
        }

        return $this->categoryRepository->delete($id);
    }

    public function getCategoriesPaginated(int $page, int $limit, ?string $search = null): array
    {
        return $this->categoryRepository->findAllPaginated($page, $limit, $search);
    }
}
