<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\InventoryBatch;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Mappers\InventoryBatchMapper;

class InventoryBatchRepository implements InventoryBatchRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    public function findActiveBatchesByProductId(string $productId, bool $lock = false): array
    {
        $query = InventoryBatchModel::where('product_id', $productId)
            ->where('status', '!=', 'inactive')
            ->whereRaw('initial_count > sold_count')
            ->orderBy('created_at', 'asc'); // FIFO (First In First Out)

        if ($lock) {
            $query->lockForUpdate();
        }

        $models = $query->get();
        return $models->map(fn($m) => InventoryBatchMapper::toDomain($m))->toArray();
    }

    public function countAvailableByProductId(string $productId): int
    {
        return (int) InventoryBatchModel::where('product_id', $productId)
            ->where('status', '!=', 'inactive')
            ->selectRaw('SUM(initial_count - sold_count) as total')
            ->value('total') ?? 0;
    }

    public function save(InventoryBatch $batch): InventoryBatch
    {
        $model = InventoryBatchMapper::toPersistence($batch);
        $model->save();

        $this->syncInventoryItems($model, $batch->getKeysPool());

        return InventoryBatchMapper::toDomain($model);
    }

    private function syncInventoryItems(InventoryBatchModel $model, ?string $keysPool): void
    {
        if ($keysPool === null) {
            return;
        }

        $lines = preg_split('/\r\n|\r|\n/', $keysPool);
        $cleanLines = array_values(array_filter(array_map('trim', $lines), fn($k) => !empty($k)));

        // Fetch currently available items for this batch
        $availableItems = \App\Infrastructure\Persistence\Eloquent\InventoryItemModel::where('batch_id', $model->id)
            ->where('status', 'available')
            ->get();

        $existingAvailableSecrets = $availableItems->pluck('secret_data')->toArray();

        // 1. Mark items as sold if they are no longer in keysPool
        foreach ($availableItems as $item) {
            if (!in_array($item->secret_data, $cleanLines, true)) {
                $item->status = 'sold';
                $item->sold_at = now();
                $item->save();
            }
        }

        // 2. Add new items if cleanLines contains new secrets not currently available
        $now = now();
        $itemsToInsert = [];
        foreach ($cleanLines as $line) {
            if (!in_array($line, $existingAvailableSecrets, true)) {
                $normalized = \Illuminate\Support\Str::slug($line, '');
                $itemsToInsert[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid7(),
                    'batch_id' => $model->id,
                    'product_id' => $model->product_id,
                    'secret_data' => $line,
                    'normalized_secret_data' => substr($normalized, 0, 255),
                    'status' => 'available',
                    'order_id' => null,
                    'sold_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($itemsToInsert)) {
            \Illuminate\Support\Facades\DB::table('inventory_items')->insert($itemsToInsert);
        }
    }

    public function findAll(array $filters): array
    {
        $query = InventoryBatchModel::query();

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'batch_name', $search);
                $q->orWhereHas('product', function ($pq) use ($search) {
                    $this->applyAccentInsensitiveSearch($pq, 'name', $search);
                });
            });
        }

        $models = $query->orderBy('created_at', 'desc')->get();
        return $models->map(fn($m) => InventoryBatchMapper::toDomain($m))->toArray();
    }

    public function findAllPaginated(array $filters, int $page, int $limit): array
    {
        $query = InventoryBatchModel::query();

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'batch_name', $search);
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
            'items' => $models->map(fn($m) => InventoryBatchMapper::toDomain($m))->toArray(),
            'total' => $total
        ];
    }

    public function findById(string $id): ?InventoryBatch
    {
        $model = InventoryBatchModel::find($id);
        return $model ? InventoryBatchMapper::toDomain($model) : null;
    }

    public function delete(string $id): bool
    {
        $model = InventoryBatchModel::find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }
}
