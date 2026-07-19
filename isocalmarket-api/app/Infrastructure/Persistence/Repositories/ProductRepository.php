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
                ->select('id', 'product_id', 'initial_count', 'sold_count', 'status', 'created_at')
                ->get();

        $total = 0;
        foreach ($batches as $batch) {
            $total += max(0, (int)$batch->initial_count - (int)$batch->sold_count);
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
            if ($categoryId === 'null' || $categoryId === 'none' || $categoryId === 'other') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $categoryId);
            }
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
            });
        }
        $models = $query->with(['activeBatches:id,product_id,initial_count,sold_count,status,created_at'])->orderBy('created_at', 'desc')->get();
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
            if ($categoryId === 'null' || $categoryId === 'none' || $categoryId === 'other') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $categoryId);
            }
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
            });
        }
        $total = $query->count();
        $models = $query->with(['activeBatches:id,product_id,initial_count,sold_count,status,created_at'])->orderBy('created_at', 'desc')
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
        $model = ProductModel::withTrashed()->where('slug', $slug)->first();
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

    public function findGroupedActiveProducts(int $limit, ?string $search = null): array
    {
        // 1. Lấy toàn bộ categories active
        $categories = \App\Infrastructure\Persistence\Eloquent\CategoryModel::orderBy('name', 'asc')->get();

        $grouped = [];

        // 2. Query sản phẩm cho từng danh mục
        foreach ($categories as $cat) {
            $query = ProductModel::query()
                ->where('status', 'active')
                ->where('category_id', $cat->id);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $this->applyAccentInsensitiveSearch($q, 'name', $search);
                });
            }

            $total = $query->count();
            if ($total > 0) {
                $models = $query->with(['activeBatches:id,product_id,initial_count,sold_count,status,created_at'])
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();

                $products = [];
                foreach ($models as $model) {
                    $availableCount = $this->getAvailableCount($model);
                    $products[] = ProductMapper::toDomain($model, $availableCount);
                }

                $grouped[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'description' => $cat->description,
                    'products' => $products,
                    'total' => $total
                ];
            }
        }

        // 3. Query sản phẩm không có danh mục (other)
        $queryOther = ProductModel::query()
            ->where('status', 'active')
            ->whereNull('category_id');

        if ($search) {
            $queryOther->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
            });
        }

        $totalOther = $queryOther->count();
        if ($totalOther > 0) {
            $modelsOther = $queryOther->with(['activeBatches:id,product_id,initial_count,sold_count,status,created_at'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();

            $productsOther = [];
            foreach ($modelsOther as $model) {
                $availableCount = $this->getAvailableCount($model);
                $productsOther[] = ProductMapper::toDomain($model, $availableCount);
            }

            $grouped[] = [
                'id' => 'other',
                'name' => 'Sản phẩm khác',
                'description' => 'Các sản phẩm chưa được xếp vào danh mục cụ thể.',
                'products' => $productsOther,
                'total' => $totalOther
            ];
        }

        return $grouped;
    }
}
