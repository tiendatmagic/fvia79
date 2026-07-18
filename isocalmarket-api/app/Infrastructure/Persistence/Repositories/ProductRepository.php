<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Mappers\ProductMapper;

class ProductRepository implements ProductRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    private function getAvailableCount(ProductModel $model): int
    {
        $batches = $model->relationLoaded('activeBatches')
            ? $model->activeBatches
            : InventoryBatchModel::where('product_id', $model->id)
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

    public function findAllActive(?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active'): array
    {
        $query = ProductModel::query();
        if ($filterDeleted === 'deleted') {
            $query->onlyTrashed();
        } elseif ($filterDeleted === 'all') {
            $query->withTrashed();
        }

        if ($status) {
            $query->where('status', $status);
        } elseif ($filterDeleted !== 'deleted') {
            $query->where('status', 'active');
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
            });
        }
        $models = $query->with(['activeBatches'])->orderBy('created_at', 'desc')->get();
        $products = [];
        foreach ($models as $model) {
            $availableCount = $this->getAvailableCount($model);
            $products[] = ProductMapper::toDomain($model, $availableCount);
        }
        return $products;
    }

    public function findAllActivePaginated(int $page, int $limit, ?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active'): array
    {
        $query = ProductModel::query();
        if ($filterDeleted === 'deleted') {
            $query->onlyTrashed();
        } elseif ($filterDeleted === 'all') {
            $query->withTrashed();
        }

        if ($status) {
            $query->where('status', $status);
        } elseif ($filterDeleted !== 'deleted') {
            $query->where('status', 'active');
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
            });
        }
        $total = $query->count();
        $models = $query->with(['activeBatches'])->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $products = [];
        foreach ($models as $model) {
            $availableCount = $this->getAvailableCount($model);
            $products[] = ProductMapper::toDomain($model, $availableCount);
        }

        return [
            'items' => $products,
            'total' => $total
        ];
    }

    public function findById(string $id): ?Product
    {
        $model = ProductModel::withTrashed()->find($id);
        if (!$model) return null;

        $availableCount = $this->getAvailableCount($model);
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::where('slug', $slug)->first();
        if (!$model) return null;

        $availableCount = $this->getAvailableCount($model);
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function save(Product $product): Product
    {
        $model = ProductMapper::toPersistence($product);
        $model->save();

        $availableCount = $this->getAvailableCount($model);
        return ProductMapper::toDomain($model, $availableCount);
    }

    public function delete(string $id): bool
    {
        $model = ProductModel::withTrashed()->find($id);
        if ($model) {
            return (bool) $model->delete();
        }
        return false;
    }

    public function restore(string $id): bool
    {
        $model = ProductModel::onlyTrashed()->find($id);
        if ($model) {
            return (bool) $model->restore();
        }
        return false;
    }
}
