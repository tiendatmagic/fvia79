<?php

namespace App\Application\Services;

use App\Domain\Entities\InventoryBatch;
use App\Domain\Entities\InventoryItem;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class InventoryService
{
    private InventoryBatchRepositoryInterface $inventoryRepository;
    private InventoryItemRepositoryInterface $inventoryItemRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        InventoryBatchRepositoryInterface $inventoryRepository,
        InventoryItemRepositoryInterface $inventoryItemRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->productRepository = $productRepository;
    }

    public function importBatch(string $productId, string $batchName, string $rawData, ?float $costPrice = null): InventoryBatch
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new \Exception('Product not found.');
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawData);
        $cleanKeys = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed)) {
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
        return $saved;
    }

    public function updateBatch(string $id, array $data): ?InventoryBatch
    {
        $batch = $this->inventoryRepository->findById($id);
        if (!$batch) {
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
        $cleanKeys = array_filter(array_map('trim', $lines), fn($k) => !empty($k));
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
            new \DateTime(),
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
        if (!$item) {
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
            $availableCount = \App\Infrastructure\Persistence\Eloquent\InventoryItemModel::where('batch_id', $batch->getId())
                ->where('status', 'available')
                ->count();

            $soldCount = $this->inventoryItemRepository->countSoldByBatchId($batch->getId());
            $initialCount = $availableCount + $soldCount;
            $status = $availableCount > 0 ? 'active' : 'completed';

            // Update directly to avoid recursively synchronizing the batch.
            \App\Infrastructure\Persistence\Eloquent\InventoryBatchModel::where('id', $batch->getId())->update([
                'initial_count' => $initialCount,
                'sold_count' => $soldCount,
                'status' => $status
            ]);
        }

        Cache::forget('products_active_storefront');
        return $saved;
    }

    public function deleteResourceItem(string $id): bool
    {
        $item = $this->inventoryItemRepository->findById($id);
        if (!$item) {
            return false;
        }

        $batchId = $item->getBatchId();
        $deleted = $this->inventoryItemRepository->delete($id);

        if ($deleted && $batchId) {
            $availableCount = \App\Infrastructure\Persistence\Eloquent\InventoryItemModel::where('batch_id', $batchId)
                ->where('status', 'available')
                ->count();

            $soldCount = $this->inventoryItemRepository->countSoldByBatchId($batchId);
            $initialCount = $availableCount + $soldCount;
            $status = $availableCount > 0 ? 'active' : 'completed';

            \App\Infrastructure\Persistence\Eloquent\InventoryBatchModel::where('id', $batchId)->update([
                'initial_count' => $initialCount,
                'sold_count' => $soldCount,
                'status' => $status
            ]);

            Cache::forget('products_active_storefront');
        }

        return $deleted;
    }
}
