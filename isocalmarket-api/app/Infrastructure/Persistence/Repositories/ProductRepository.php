<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Mappers\ProductMapper;

class ProductRepository implements ProductRepositoryInterface
{
    public function findAllActive(?string $categoryId = null): array
    {
        $query = ProductModel::where('status', 'active');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        $models = $query->orderBy('created_at', 'desc')->get();
        $products = [];
        foreach ($models as $model) {
            $availableCount = InventoryItemModel::where('product_id', $model->id)
                ->where('status', 'available')
                ->count();
            $products[] = ProductMapper::toDomain($model, $availableCount);
        }
        return $products;
    }

    public function findAllActivePaginated(int $page, int $limit, ?string $categoryId = null): array
    {
        $query = ProductModel::where('status', 'active');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        $total = $query->count();
        $models = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $products = [];
        foreach ($models as $model) {
            $availableCount = InventoryItemModel::where('product_id', $model->id)
                ->where('status', 'available')
                ->count();
            $products[] = ProductMapper::toDomain($model, $availableCount);
        }

        return [
            'items' => $products,
            'total' => $total
        ];
    }

    public function findById(string $id): ?Product
    {
        $model = ProductModel::find($id);
        if (!$model) return null;

        $availableCount = InventoryItemModel::where('product_id', $model->id)
            ->where('status', 'available')
            ->count();
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::where('slug', $slug)->first();
        if (!$model) return null;

        $availableCount = InventoryItemModel::where('product_id', $model->id)
            ->where('status', 'available')
            ->count();
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function save(Product $product): Product
    {
        $model = ProductMapper::toPersistence($product);
        $model->save();

        $availableCount = InventoryItemModel::where('product_id', $model->id)
            ->where('status', 'available')
            ->count();
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function delete(string $id): bool
    {
        $model = ProductModel::find($id);
        if ($model) {
            return (bool) $model->delete();
        }
        return false;
    }
}
