<?php

namespace App\Application\Services;

use App\Domain\Entities\InventoryBatch;
use App\Domain\Entities\InventoryItem;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    private InventoryBatchRepositoryInterface $inventoryRepository;

    private InventoryItemRepositoryInterface $inventoryItemRepository;

    private ProductRepositoryInterface $productRepository;

    private TelegramNotificationService $telegramService;

    public function __construct(
        InventoryBatchRepositoryInterface $inventoryRepository,
        InventoryItemRepositoryInterface $inventoryItemRepository,
        ProductRepositoryInterface $productRepository,
        ?TelegramNotificationService $telegramService = null
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->productRepository = $productRepository;
        $this->telegramService = $telegramService ?? app(TelegramNotificationService::class);
    }

    public function importBatch(string $productId, string $batchName, string $rawData, ?float $costPrice = null): InventoryBatch
    {
        $product = $this->productRepository->findById($productId);
        if (! $product) {
            throw new \Exception('Product not found.');
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawData);
        $cleanKeys = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (! empty($trimmed)) {
                $cleanKeys[] = $trimmed;
            }
        }

        $initialCount = count($cleanKeys);
        if ($initialCount === 0) {
            throw new \Exception('No resource keys found in raw data.');
        }

        $batch = new InventoryBatch(
            null,
            $productId,
            $batchName,
            implode("\n", $cleanKeys),
            $initialCount,
            0,
            'active',
            null,
            null,
            $costPrice
        );

        $saved = $this->inventoryRepository->save($batch);
        Cache::forget('products_active_storefront');

        try {
            $this->telegramService->notifyNewInventoryBatch($product, $saved);
        } catch (\Throwable $e) {
            // Log & ignore notification failures to prevent blocking inventory import
            Log::error('Telegram inventory notification error: '.$e->getMessage());
        }

        return $saved;
    }

    public function updateBatch(string $id, array $data): ?InventoryBatch
    {
        $batch = $this->inventoryRepository->findById($id);
        if (! $batch) {
            return null;
        }

        $lastUpdatedAt = $data['last_updated_at'] ?? null;
        if ($lastUpdatedAt) {
            $dbUpdatedAt = $batch->getUpdatedAt()?->format('Y-m-d H:i:s');
            if ($dbUpdatedAt && $dbUpdatedAt !== $lastUpdatedAt) {
                throw new \Exception('This inventory batch was updated by another transaction. Close the modal and reload the page.');
            }
        }

        $productId = $data['product_id'] ?? $batch->getProductId();
        $batchName = $data['batch_name'] ?? $batch->getBatchName();
        $keysPool = $data['keys_pool'] ?? $batch->getKeysPool();
        $status = $data['status'] ?? $batch->getStatus();
        $costPrice = array_key_exists('cost_price', $data) ? ($data['cost_price'] !== null ? (float) $data['cost_price'] : null) : $batch->getCostPrice();

        $lines = preg_split('/\r\n|\r|\n/', $keysPool);
        $cleanKeys = array_filter(array_map('trim', $lines), fn ($k) => ! empty($k));
        $currentAvailableCount = count($cleanKeys);

        $soldCount = $batch->getSoldCount();
        $initialCount = $currentAvailableCount + $soldCount;

        if ($currentAvailableCount > 0 && $status !== 'inactive') {
            $status = 'active';
        } elseif ($currentAvailableCount === 0 && $status === 'active') {
            $status = 'completed';
        }

        $updatedBatch = new InventoryBatch(
            $batch->getId(),
            $productId,
            $batchName,
            implode("\n", $cleanKeys),
            $initialCount,
            $soldCount,
            $status,
            $batch->getCreatedAt(),
            new \DateTime,
            $costPrice
        );

        $saved = $this->inventoryRepository->save($updatedBatch);
        Cache::forget('products_active_storefront');

        return $saved;
    }

    public function getItemById(string $id): ?InventoryBatch
    {
        return $this->inventoryRepository->findById($id);
    }

    public function getItems(array $filters): array
    {
        return $this->inventoryRepository->findAll($filters);
    }

    public function getItemsPaginated(array $filters, int $page, int $limit): array
    {
        return $this->inventoryRepository->findAllPaginated($filters, $page, $limit);
    }

    public function deleteItem(string $id): bool
    {
        $deleted = $this->inventoryRepository->delete($id);
        if ($deleted) {
            Cache::forget('products_active_storefront');
        }

        return $deleted;
    }

    public function getResourceItemsPaginated(array $filters, int $page, int $limit): array
    {
        return $this->inventoryItemRepository->findAllPaginated($filters, $page, $limit);
    }

    public function getResourceItemById(string $id): ?InventoryItem
    {
        return $this->inventoryItemRepository->findById($id);
    }

    public function updateResourceItem(string $id, array $data): ?InventoryItem
    {
        $item = $this->inventoryItemRepository->findById($id);
        if (! $item) {
            return null;
        }

        if (isset($data['secret_data'])) {
            $item->setSecretData(trim($data['secret_data']));
        }
        if (isset($data['status'])) {
            $item->setStatus($data['status']);
        }

        $saved = $this->inventoryItemRepository->save($item);

        $batch = $this->inventoryRepository->findById($item->getBatchId());
        if ($batch) {
            $availableCount = InventoryItemModel::where('batch_id', $batch->getId())
                ->where('status', 'available')
                ->count();

            $soldCount = $this->inventoryItemRepository->countSoldByBatchId($batch->getId());
            $initialCount = $availableCount + $soldCount;
            $status = $availableCount > 0 ? 'active' : 'completed';

            // Update directly to avoid recursively synchronizing the batch.
            InventoryBatchModel::where('id', $batch->getId())->update([
                'initial_count' => $initialCount,
                'sold_count' => $soldCount,
                'status' => $status,
            ]);
        }

        Cache::forget('products_active_storefront');

        return $saved;
    }

    public function deleteResourceItem(string $id): bool
    {
        $item = $this->inventoryItemRepository->findById($id);
        if (! $item) {
            return false;
        }

        $batchId = $item->getBatchId();
        $deleted = $this->inventoryItemRepository->delete($id);

        if ($deleted && $batchId) {
            $availableCount = InventoryItemModel::where('batch_id', $batchId)
                ->where('status', 'available')
                ->count();

            $soldCount = $this->inventoryItemRepository->countSoldByBatchId($batchId);
            $initialCount = $availableCount + $soldCount;
            $status = $availableCount > 0 ? 'active' : 'completed';

            InventoryBatchModel::where('id', $batchId)->update([
                'initial_count' => $initialCount,
                'sold_count' => $soldCount,
                'status' => $status,
            ]);

            Cache::forget('products_active_storefront');
        }

        return $deleted;
    }

    public function bulkUpdateResourceItemStatus(array $resourceIds, string $status): int
    {
        return DB::transaction(function () use ($resourceIds, $status): int {
            $items = InventoryItemModel::whereIn('id', array_unique($resourceIds))
                ->lockForUpdate()
                ->get(['id', 'batch_id']);

            if ($items->isEmpty()) {
                return 0;
            }

            $updates = ['status' => $status];
            if ($status === 'available') {
                $updates += ['order_id' => null, 'sold_at' => null];
            } else {
                $updates['sold_at'] = now();
            }

            $updatedCount = InventoryItemModel::whereIn('id', $items->pluck('id'))->update($updates);
            $this->syncBatchCounters($items->pluck('batch_id')->all());
            Cache::forget('products_active_storefront');

            return $updatedCount;
        });
    }

    public function bulkDeleteResourceItems(array $resourceIds): int
    {
        return DB::transaction(function () use ($resourceIds): int {
            $items = InventoryItemModel::whereIn('id', array_unique($resourceIds))
                ->lockForUpdate()
                ->get(['id', 'batch_id']);

            if ($items->isEmpty()) {
                return 0;
            }

            $deletedCount = InventoryItemModel::whereIn('id', $items->pluck('id'))->delete();
            $this->syncBatchCounters($items->pluck('batch_id')->all());
            Cache::forget('products_active_storefront');

            return $deletedCount;
        });
    }

    private function syncBatchCounters(array $batchIds): void
    {
        $batchIds = array_values(array_unique(array_filter($batchIds)));
        if ($batchIds === []) {
            return;
        }

        $batches = InventoryBatchModel::whereKey($batchIds)
            ->lockForUpdate()
            ->get(['id', 'product_id', 'batch_name', 'status', 'created_at']);
        if ($batches->isEmpty()) {
            return;
        }

        $countsByBatch = InventoryItemModel::query()
            ->whereIn('batch_id', $batches->pluck('id'))
            ->select('batch_id')
            ->selectRaw('COUNT(*) AS initial_count')
            ->selectRaw("SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_count")
            ->selectRaw("SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold_count")
            ->groupBy('batch_id')
            ->get()
            ->keyBy('batch_id');

        $now = now();
        $updates = $batches->map(function ($batch) use ($countsByBatch, $now) {
            $counts = $countsByBatch->get($batch->id);
            $availableCount = (int) ($counts?->available_count ?? 0);

            return [
                'id' => $batch->id,
                'product_id' => $batch->product_id,
                'batch_name' => $batch->batch_name,
                'initial_count' => (int) ($counts?->initial_count ?? 0),
                'sold_count' => (int) ($counts?->sold_count ?? 0),
                'status' => $batch->status === 'inactive'
                    ? 'inactive'
                    : ($availableCount > 0 ? 'active' : 'completed'),
                'updated_at' => $now,
                'created_at' => $batch->created_at,
            ];
        })->all();

        DB::table('inventory_batches')->upsert(
            $updates,
            ['id'],
            ['initial_count', 'sold_count', 'status', 'updated_at'],
        );
    }
}
