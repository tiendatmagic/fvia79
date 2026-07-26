<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Mappers\CategoryMapper;

class CategoryRepository implements CategoryRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    public function findAll(?string $search = null): array
    {
        $query = CategoryModel::query()->withCount('products');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
                $q->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_vi', 'like', "%{$search}%");
            });
        }
        $models = $query->orderBy('name', 'asc')->get();
        $categories = [];
        foreach ($models as $model) {
            $categories[] = CategoryMapper::toDomain($model);
        }

        return $categories;
    }

    public function findAllPaginated(int $page, int $limit, ?string $search = null): array
    {
        $query = CategoryModel::query()->withCount('products');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
                $q->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_vi', 'like', "%{$search}%");
            });
        }
        $total = $query->count();
        $models = $query->orderBy('name', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $categories = [];
        foreach ($models as $model) {
            $categories[] = CategoryMapper::toDomain($model);
        }

        return [
            'items' => $categories,
            'total' => $total,
        ];
    }

    public function findById(string $id): ?Category
    {
        $model = CategoryModel::find($id);
        if (! $model) {
            return null;
        }

        return CategoryMapper::toDomain($model);
    }

    public function findBySlug(string $slug): ?Category
    {
        $model = CategoryModel::where('slug', $slug)->first();
        if (! $model) {
            return null;
        }

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
