<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Mappers\CategoryMapper;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function findAll(): array
    {
        $models = CategoryModel::orderBy('name', 'asc')->get();
        $categories = [];
        foreach ($models as $model) {
            $categories[] = CategoryMapper::toDomain($model);
        }
        return $categories;
    }

    public function findById(string $id): ?Category
    {
        $model = CategoryModel::find($id);
        if (!$model) return null;
        return CategoryMapper::toDomain($model);
    }

    public function findBySlug(string $slug): ?Category
    {
        $model = CategoryModel::where('slug', $slug)->first();
        if (!$model) return null;
        return CategoryMapper::toDomain($model);
    }

    public function save(Category $category): Category
    {
        $model = CategoryMapper::toPersistence($category);
        $model->save();
        return CategoryMapper::toDomain($model);
    }

    public function delete(string $id): bool
    {
        $model = CategoryModel::find($id);
        if ($model) {
            return (bool) $model->delete();
        }
        return false;
    }
}
