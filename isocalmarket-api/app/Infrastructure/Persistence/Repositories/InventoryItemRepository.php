<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\InventoryItem;
use App\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Mappers\InventoryItemMapper;
use Illuminate\Support\Facades\DB;

class InventoryItemRepository implements InventoryItemRepositoryInterface
{
    public function findAvailableByProductId(string $productId, int $limit): array
    {
        $models = InventoryItemModel::where('product_id', $productId)
            ->where('status', 'available')
            ->limit($limit)
            ->get();

        return $models->map(fn($m) => InventoryItemMapper::toDomain($m))->toArray();
    }

    public function findAvailableByProductIdForUpdate(string $productId, int $limit): array
    {
        $models = InventoryItemModel::where('product_id', $productId)
            ->where('status', 'available')
            ->lockForUpdate()
            ->limit($limit)
            ->get();

        return $models->map(fn($m) => InventoryItemMapper::toDomain($m))->toArray();
    }

    public function countAvailableByProductId(string $productId): int
    {
        return InventoryItemModel::where('product_id', $productId)
            ->where('status', 'available')
            ->count();
    }

    public function save(InventoryItem $item): InventoryItem
    {
        $model = InventoryItemMapper::toPersistence($item);
        $model->save();
        return InventoryItemMapper::toDomain($model);
    }

    public function saveMany(array $items): bool
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $model = InventoryItemMapper::toPersistence($item);
                $model->save();
            }
        });
        return true;
    }

    public function findByOrderId(string $orderId): array
    {
        $models = InventoryItemModel::where('order_id', $orderId)->get();
        return $models->map(fn($m) => InventoryItemMapper::toDomain($m))->toArray();
    }

    public function findAll(array $filters): array
    {
        $query = InventoryItemModel::query();

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $models = $query->orderBy('created_at', 'desc')->get();
        return $models->map(fn($m) => InventoryItemMapper::toDomain($m))->toArray();
    }

    public function findAllPaginated(array $filters, int $page, int $limit): array
    {
        $query = InventoryItemModel::query();

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $total = $query->count();
        $models = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'items' => $models->map(fn($m) => InventoryItemMapper::toDomain($m))->toArray(),
            'total' => $total
        ];
    }

    public function findById(string $id): ?InventoryItem
    {
        $model = InventoryItemModel::find($id);
        return $model ? InventoryItemMapper::toDomain($model) : null;
    }

    public function delete(string $id): bool
    {
        $model = InventoryItemModel::find($id);
        if ($model) {
            return (bool) $model->delete();
        }
        return false;
    }
}
