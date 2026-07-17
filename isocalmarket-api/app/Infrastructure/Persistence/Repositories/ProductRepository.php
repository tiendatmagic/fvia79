<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Mappers\ProductMapper;

class ProductRepository implements ProductRepositoryInterface
{
    private function getAvailableCount(string $productId): int
    {
        $batches = InventoryBatchModel::where('product_id', $productId)
            ->where('status', 'active')
            ->get();
        $total = 0;
        foreach ($batches as $batch) {
            if (!empty($batch->keys_pool)) {
                $lines = preg_split('/\r\n|\r|\n/', trim($batch->keys_pool));
                $filtered = array_filter($lines, fn($line) => !empty(trim($line)));
                $total += count($filtered);
            }
        }
        return $total;
    }

    public function findAllActive(?string $categoryId = null): array
    {
        $query = ProductModel::where('status', 'active');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        $models = $query->orderBy('created_at', 'desc')->get();
        $products = [];
        foreach ($models as $model) {
            $availableCount = $this->getAvailableCount($model->id);
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
            $availableCount = $this->getAvailableCount($model->id);
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

        $availableCount = $this->getAvailableCount($model->id);
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::where('slug', $slug)->first();
        if (!$model) return null;

        $availableCount = $this->getAvailableCount($model->id);
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function save(Product $product): Product
    {
        $model = ProductMapper::toPersistence($product);
        $model->save();

        $availableCount = $this->getAvailableCount($model->id);
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
