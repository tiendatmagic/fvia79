<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\InventoryItem;
use App\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Mappers\InventoryItemMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryItemRepository implements InventoryItemRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    public function save(InventoryItem $item): InventoryItem
    {
        $model = InventoryItemMapper::toPersistence($item);
        $model->save();
        $model->load(['batch', 'product']);
        return InventoryItemMapper::toDomain($model);
    }

    public function findById(string $id): ?InventoryItem
    {
        $model = InventoryItemModel::with(['batch', 'product'])->find($id);
        return $model ? InventoryItemMapper::toDomain($model) : null;
    }

    public function findAllPaginated(array $filters, int $page, int $limit): array
    {
        $query = InventoryItemModel::with(['batch', 'product']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'secret_data', $search);
                $q->orWhereHas('batch', function ($bq) use ($search) {
                    $this->applyAccentInsensitiveSearch($bq, 'batch_name', $search);
                });
                $q->orWhereHas('product', function ($pq) use ($search) {
                    $this->applyAccentInsensitiveSearch($pq, 'name', $search);
                });
            });
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

    public function delete(string $id): bool
    {
        $model = InventoryItemModel::find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }

    public function bulkInsert(array $items): bool
    {
        if (empty($items)) {
            return true;
        }

        $now = now();
        $records = [];
        foreach ($items as $item) {
            $secret = is_array($item) ? ($item['secret_data'] ?? '') : $item->getSecretData();
            $batchId = is_array($item) ? ($item['batch_id'] ?? '') : $item->getBatchId();
            $productId = is_array($item) ? ($item['product_id'] ?? '') : $item->getProductId();
            $status = is_array($item) ? ($item['status'] ?? 'available') : $item->getStatus();

            $normalized = Str::slug($secret, '');

            $records[] = [
                'id' => (string) Str::uuid7(),
                'batch_id' => $batchId,
                'product_id' => $productId,
                'secret_data' => $secret,
                'normalized_secret_data' => substr($normalized, 0, 255),
                'status' => $status,
                'order_id' => null,
                'sold_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return DB::table('inventory_items')->insert($records);
    }

    public function deleteAvailableByBatchId(string $batchId): int
    {
        return InventoryItemModel::where('batch_id', $batchId)
            ->where('status', 'available')
            ->delete();
    }

    public function popAvailableItems(string $productId, int $quantity, string $orderId): array
    {
        return DB::transaction(function () use ($productId, $quantity, $orderId) {
            $models = InventoryItemModel::where('product_id', $productId)
                ->where('status', 'available')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->take($quantity)
                ->get();

            if ($models->count() < $quantity) {
                return [];
            }

            $now = now();
            foreach ($models as $model) {
                $model->status = 'sold';
                $model->order_id = $orderId;
                $model->sold_at = $now;
                $model->save();
            }

            return $models->map(fn($m) => InventoryItemMapper::toDomain($m))->toArray();
        });
    }

    public function countAvailableByProductId(string $productId): int
    {
        return InventoryItemModel::where('product_id', $productId)
            ->where('status', 'available')
            ->count();
    }

    public function countAvailableByBatchId(string $batchId): int
    {
        return InventoryItemModel::where('batch_id', $batchId)
            ->where('status', 'available')
            ->count();
    }

    public function countSoldByBatchId(string $batchId): int
    {
        return InventoryItemModel::where('batch_id', $batchId)
            ->where('status', 'sold')
            ->count();
    }
}
