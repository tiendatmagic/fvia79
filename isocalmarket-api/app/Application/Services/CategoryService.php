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

    public function createCategory(string $name, ?string $slug, ?string $description): Category
    {
        $finalSlug = $slug ? Str::slug($slug) : Str::slug($name);
        $existing = $this->categoryRepository->findBySlug($finalSlug);
        if ($existing) {
            $finalSlug .= '-' . time();
        }

        $category = new Category(null, $name, $finalSlug, $description);
        return $this->categoryRepository->save($category);
    }

    public function updateCategory(string $id, string $name, ?string $slug, ?string $description): Category
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \Exception('Category not found.');
        }

        $finalSlug = $slug ? Str::slug($slug) : Str::slug($name);
        if ($finalSlug !== $category->getSlug()) {
            $existing = $this->categoryRepository->findBySlug($finalSlug);
            if ($existing) {
                $finalSlug .= '-' . time();
            }
        }

        $updatedCategory = new Category($id, $name, $finalSlug, $description);
        return $this->categoryRepository->save($updatedCategory);
    }

    public function deleteCategory(string $id): bool
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \Exception('Category not found.');
        }

        // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
        $hasProducts = ProductModel::where('category_id', $id)->exists();
        if ($hasProducts) {
            throw new \Exception('Cannot delete category containing active products.');
        }

        return $this->categoryRepository->delete($id);
    }

    public function getCategoriesPaginated(int $page, int $limit, ?string $search = null): array
    {
        return $this->categoryRepository->findAllPaginated($page, $limit, $search);
    }
}
