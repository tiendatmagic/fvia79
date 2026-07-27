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

        if ($status === 'active') {
            $query->where(function ($q) {
                $q->whereNull('category_id')
                    ->orWhereHas('category', fn ($catQ) => $catQ->where('is_active', true));
            });
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
                $q->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_vi', 'like', "%{$search}%");
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

    public function findAllActivePaginated(int $page, int $limit, ?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active', bool $includeOutOfStock = false): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);
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

        if ($status === 'active') {
            $query->where(function ($q) {
                $q->whereNull('category_id')
                    ->orWhereHas('category', function ($catQ) {
                        $catQ->where('is_active', true);
                    });
            });
        }

        if ($categoryId) {
            if ($categoryId === 'null' || $categoryId === 'none' || $categoryId === 'other') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $categoryId);
            }
        }
        $isSearching = $search !== null && trim($search) !== '';
        if ($isSearching) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
                $q->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_vi', 'like', "%{$search}%");
            });
        }
        $models = $query->with(['supplier:id,code,name,enabled,products_enabled', 'activeBatches:id,product_id,initial_count,sold_count,status,created_at'])->orderBy('created_at', 'desc')->get();

        $filtered = [];
        foreach ($models as $model) {
            $availableCount = $this->getAvailableCount($model);
            if ($isSearching && !$includeOutOfStock && $availableCount <= 0) {
                continue;
            }
            $filtered[] = ProductMapper::toDomain($model, $availableCount);
        }

        $total = count($filtered);
        $paged = array_slice($filtered, ($page - 1) * $limit, $limit);

        return [
            'items' => $paged,
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

    public function findGroupedActiveProducts(int $limit, ?string $search = null, bool $includeOutOfStock = false): array
    {
        $limit = max(1, $limit);
        $categories = CategoryModel::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        $productQuery = ProductModel::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('category_id')
                    ->orWhereHas('category', function ($catQ) {
                        $catQ->where('is_active', true);
                    });
            });

        $isSearching = $search !== null && trim($search) !== '';
        if ($isSearching) {
            $search = trim($search);
            $productQuery->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'name', $search);
                $q->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_vi', 'like', "%{$search}%");
            });
        }

        $allActiveProducts = (clone $productQuery)
            ->with(['supplier:id,code,name,enabled,products_enabled', 'activeBatches:id,product_id,initial_count,sold_count,status,created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filteredProducts = [];
        foreach ($allActiveProducts as $model) {
            $availableCount = $this->getAvailableCount($model);

            if ($isSearching && !$includeOutOfStock && $availableCount <= 0) {
                continue;
            }

            $filteredProducts[] = [
                'model' => $model,
                'available_count' => $availableCount,
                'category_id' => (string) ($model->category_id ?? 'other'),
            ];
        }

        $productsByCat = collect($filteredProducts)->groupBy('category_id');

        $grouped = [];

        foreach ($categories as $cat) {
            $catId = (string) $cat->id;
            $catEntries = isset($productsByCat[$catId]) ? $productsByCat[$catId] : collect();
            $total = $catEntries->count();

            if ($isSearching && $total === 0) {
                continue;
            }

            $catProductsModels = $catEntries->take($limit);
            $products = [];
            foreach ($catProductsModels as $entry) {
                $products[] = ProductMapper::toDomain($entry['model'], $entry['available_count']);
            }

            $grouped[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'description' => $cat->description,
                'products' => $products,
                'total' => $total,
            ];
        }

        if (isset($productsByCat['other'])) {
            $otherEntries = $productsByCat['other'];
            $totalOther = $otherEntries->count();

            if (!$isSearching || $totalOther > 0) {
                $otherModels = $otherEntries->take($limit);
                $productsOther = [];
                foreach ($otherModels as $entry) {
                    $productsOther[] = ProductMapper::toDomain($entry['model'], $entry['available_count']);
                }

                $grouped[] = [
                    'id' => 'other',
                    'name' => 'Other Products',
                    'description' => 'Products that have not been assigned to a specific category.',
                    'products' => $productsOther,
                    'total' => $totalOther,
                ];
            }
        }

        return $grouped;
    }
}
