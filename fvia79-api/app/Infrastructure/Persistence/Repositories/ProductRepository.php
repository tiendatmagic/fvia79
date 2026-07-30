<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Mappers\ProductMapper;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository implements ProductRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    private function getAvailableCount(ProductModel $model): int
    {
        if (array_key_exists('calculated_available_count', $model->getAttributes())) {
            return max(0, (int) $model->getAttribute('calculated_available_count'));
        }

        $total = (int) InventoryBatchModel::where('product_id', $model->id)
            ->where('status', '!=', 'inactive')
            ->selectRaw('COALESCE(SUM(CASE WHEN initial_count > sold_count THEN initial_count - sold_count ELSE 0 END), 0) AS available_count')
            ->value('available_count');

        $supplierEnabled = $model->supplier_id === null
            || ($model->relationLoaded('supplier')
                ? (bool) ($model->supplier?->enabled && $model->supplier?->products_enabled)
                : (bool) $model->supplier()->where('enabled', true)->where('products_enabled', true)->exists());

        return $total + ($supplierEnabled ? max(0, (int) ($model->supplier_stock ?? 0)) : 0);
    }

    private function availableStockSql(): string
    {
        return <<<'SQL'
            (
                COALESCE((
                    SELECT SUM(
                        CASE
                            WHEN inventory_batches.initial_count > inventory_batches.sold_count
                            THEN inventory_batches.initial_count - inventory_batches.sold_count
                            ELSE 0
                        END
                    )
                    FROM inventory_batches
                    WHERE inventory_batches.product_id = products.id
                      AND inventory_batches.status != ?
                ), 0)
                +
                CASE
                    WHEN products.supplier_id IS NULL
                      OR EXISTS (
                          SELECT 1 FROM suppliers
                          WHERE suppliers.id = products.supplier_id
                            AND suppliers.enabled = 1
                            AND suppliers.products_enabled = 1
                      )
                    THEN COALESCE(products.supplier_stock, 0)
                    ELSE 0
                END
            )
            SQL;
    }

    private function addCalculatedStock(Builder $query): Builder
    {
        return $query->select('products.*')
            ->selectRaw($this->availableStockSql().' AS calculated_available_count', ['inactive'])
            ->with('supplier:id,code,name,enabled,products_enabled');
    }

    private function requireAvailableStock(Builder $query): Builder
    {
        return $query->whereRaw($this->availableStockSql().' > 0', ['inactive']);
    }

    public function findAllActive(?string $categoryId = null, ?string $status = null, ?string $search = null, ?string $filterDeleted = 'active', ?int $maxResults = null): array
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
        if ($maxResults !== null) {
            $query->limit(max(1, $maxResults));
        }
        $models = $this->addCalculatedStock($query)
            ->orderBy('created_at', 'desc')
            ->get();
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
            if ($status === 'active' && $includeOutOfStock) {
                $query->where(function ($q) {
                    $q->where('status', 'active')
                        ->orWhere('supplier_out_of_stock_hidden', true);
                });
            } else {
                $query->where('status', $status);
            }
        } elseif ($filterDeleted !== 'deleted') {
            if ($includeOutOfStock) {
                $query->where(function ($q) {
                    $q->where('status', 'active')
                        ->orWhere('supplier_out_of_stock_hidden', true);
                });
            } else {
                $query->where('status', 'active');
            }
        }

        if ($status === 'active' || ($status === null && $filterDeleted !== 'deleted')) {
            $query->where(function ($q) use ($includeOutOfStock) {
                $q->whereNull('category_id')
                    ->orWhereHas('category', function ($catQ) use ($includeOutOfStock) {
                        if (! $includeOutOfStock) {
                            $catQ->where('is_active', true);
                        }
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
        if (! $includeOutOfStock) {
            $this->requireAvailableStock($query);
        }

        $total = (clone $query)->count('products.id');
        $models = $this->addCalculatedStock($query)
            ->orderBy('created_at', 'desc')
            ->forPage($page, $limit)
            ->get();

        $paged = $models->map(
            fn (ProductModel $model) => ProductMapper::toDomain($model, $this->getAvailableCount($model))
        )->all();

        return [
            'items' => $paged,
            'total' => $total,
        ];
    }

    public function findById(string $id): ?Product
    {
        $model = $this->addCalculatedStock(ProductModel::withTrashed())->find($id);
        if (! $model) {
            return null;
        }

        $availableCount = $this->getAvailableCount($model);

        return ProductMapper::toDomain($model, $availableCount);
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = $this->addCalculatedStock(ProductModel::withTrashed())
            ->where('slug', $slug)
            ->first();
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
        $categoriesQuery = CategoryModel::query();
        if (! $includeOutOfStock) {
            $categoriesQuery->where('is_active', true);
        }
        $categories = $categoriesQuery->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        $productQuery = ProductModel::query()
            ->where(function ($q) use ($includeOutOfStock) {
                $q->where('status', 'active');
                if ($includeOutOfStock) {
                    $q->orWhere('supplier_out_of_stock_hidden', true);
                }
            })
            ->where(function ($q) use ($includeOutOfStock) {
                $q->whereNull('category_id')
                    ->orWhereHas('category', function ($catQ) use ($includeOutOfStock) {
                        if (! $includeOutOfStock) {
                            $catQ->where('is_active', true);
                        }
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

        if (! $includeOutOfStock) {
            $this->requireAvailableStock($productQuery);
        }

        $grouped = [];

        foreach ($categories as $cat) {
            $catId = (string) $cat->id;
            $categoryQuery = (clone $productQuery)->where('category_id', $cat->id);
            $total = (clone $categoryQuery)->count('products.id');

            if ($isSearching && $total === 0) {
                continue;
            }

            $products = $this->addCalculatedStock($categoryQuery)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn (ProductModel $model) => ProductMapper::toDomain($model, $this->getAvailableCount($model)))
                ->all();

            $grouped[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'name_vi' => $cat->name_vi,
                'name_en' => $cat->name_en,
                'description' => $cat->description,
                'description_vi' => $cat->description_vi,
                'description_en' => $cat->description_en,
                'products' => $products,
                'total' => $total,
            ];
        }

        $otherQuery = (clone $productQuery)->whereNull('category_id');
        $totalOther = (clone $otherQuery)->count('products.id');
        if ($totalOther > 0) {
            $productsOther = $this->addCalculatedStock($otherQuery)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn (ProductModel $model) => ProductMapper::toDomain($model, $this->getAvailableCount($model)))
                ->all();

            $grouped[] = [
                'id' => 'other',
                'name' => 'Other Products',
                'name_vi' => null,
                'name_en' => null,
                'description' => 'Products that have not been assigned to a specific category.',
                'description_vi' => null,
                'description_en' => null,
                'products' => $productsOther,
                'total' => $totalOther,
            ];
        }

        return $grouped;
    }
}
