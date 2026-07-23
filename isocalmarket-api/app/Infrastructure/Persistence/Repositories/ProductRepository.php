<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Mappers\ProductMapper;

class ProductRepository implements ProductRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    private function getAvailableCount(ProductModel $model): int
    {
        $batches = InventoryBatchModel::where('product_id', $model->id)
            ->where('status', '!=', 'inactive')
            ->select('id', 'product_id', 'initial_count', 'sold_count', 'status', 'created_at')
            ->get();

        $total = 0;
        foreach ($batches as $batch) {
            $total += max(0, (int) $batch->initial_count - (int) $batch->sold_count);
        }

        $supplierEnabled = $model->supplier_id === null
            || ($model->relationLoaded('supplier')
                ? (bool) ($model->supplier?->enabled && $model->supplier?->products_enabled)
                : (bool) $model->supplier()->where('enabled', true)->where('products_enabled', true)->exists());

        return $total + ($supplierEnabled ? max(0, (int) ($model->supplier_stock ?? 0)) : 0);
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
        $models = $query->with(['supplier:id,code,name,enabled,products_enabled', 'activeBatches:id,product_id,initial_count,sold_count,status,created_at'])->orderBy('created_at', 'desc')->get();
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
        $models = $query->with(['supplier:id,code,name,enabled,products_enabled', 'activeBatches:id,product_id,initial_count,sold_count,status,created_at'])->orderBy('created_at', 'desc')
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
            'total' => $total,
        ];
    }

    public function findById(string $id): ?Product
    {
        $model = ProductModel::withTrashed()->with(['supplier:id,code,name,enabled,products_enabled', 'activeBatches:id,product_id,initial_count,sold_count,status,created_at'])->find($id);
        if (! $model) {
            return null;
        }

        $availableCount = $this->getAvailableCount($model);

        return ProductMapper::toDomain($model, $availableCount);
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::withTrashed()->with(['supplier:id,code,name,enabled,products_enabled', 'activeBatches:id,product_id,initial_count,sold_count,status,created_at'])->where('slug', $slug)->first();
        if (! $model) {
            return null;
        }

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
        $categories = CategoryModel::orderBy('name', 'asc')->get();

        $productQuery = ProductModel::query()->where('status', 'active');
        if ($search) {
            $productQuery->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
            });
        }

        $countsByCategory = (clone $productQuery)
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        if ($countsByCategory->isEmpty()) {
            return [];
        }

        $allActiveProducts = (clone $productQuery)
            ->with(['supplier:id,code,name,enabled,products_enabled', 'activeBatches:id,product_id,initial_count,sold_count,status,created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [];
        $productsByCat = $allActiveProducts->groupBy(fn ($p) => (string) ($p->category_id ?? 'other'));

        foreach ($categories as $cat) {
            $catId = (string) $cat->id;
            if (isset($productsByCat[$catId])) {
                $catProductsModels = $productsByCat[$catId]->take($limit);
                $products = [];
                foreach ($catProductsModels as $model) {
                    $availableCount = $this->getAvailableCount($model);
                    $products[] = ProductMapper::toDomain($model, $availableCount);
                }

                $grouped[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'description' => $cat->description,
                    'products' => $products,
                    'total' => (int) ($countsByCategory[$catId] ?? count($catProductsModels)),
                ];
            }
        }

        if (isset($productsByCat['other'])) {
            $otherModels = $productsByCat['other']->take($limit);
            $productsOther = [];
            foreach ($otherModels as $model) {
                $availableCount = $this->getAvailableCount($model);
                $productsOther[] = ProductMapper::toDomain($model, $availableCount);
            }

            $totalOther = (int) ($countsByCategory[''] ?? $countsByCategory[null] ?? count($otherModels));
            $grouped[] = [
                'id' => 'other',
                'name' => 'Other Products',
                'description' => 'Products that have not been assigned to a specific category.',
                'products' => $productsOther,
                'total' => $totalOther,
            ];
        }

        return $grouped;
    }
}
