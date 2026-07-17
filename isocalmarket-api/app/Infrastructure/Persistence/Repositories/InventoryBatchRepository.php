<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\InventoryBatch;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Mappers\InventoryBatchMapper;

class InventoryBatchRepository implements InventoryBatchRepositoryInterface
{
    public function findActiveBatchesByProductId(string $productId, bool $lock = false): array
    {
        $query = InventoryBatchModel::where('product_id', $productId)
            ->where('status', 'active')
            ->orderBy('created_at', 'asc'); // FIFO (First In First Out)

        if ($lock) {
            $query->lockForUpdate();
        }

        $models = $query->get();
        return $models->map(fn($m) => InventoryBatchMapper::toDomain($m))->toArray();
    }

    public function countAvailableByProductId(string $productId): int
    {
        $batches = $this->findActiveBatchesByProductId($productId);
        $total = 0;
        foreach ($batches as $batch) {
            $total += $batch->getAvailableCount();
        }
        return $total;
    }

    public function save(InventoryBatch $batch): InventoryBatch
    {
        $model = InventoryBatchMapper::toPersistence($batch);
        $model->save();
        return InventoryBatchMapper::toDomain($model);
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
            $query->where('batch_name', 'like', '%' . $filters['search'] . '%');
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
            $query->where('batch_name', 'like', '%' . $filters['search'] . '%');
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
