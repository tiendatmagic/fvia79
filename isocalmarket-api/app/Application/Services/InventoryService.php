<?php

namespace App\Application\Services;

use App\Domain\Entities\InventoryBatch;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class InventoryService
{
    private InventoryBatchRepositoryInterface $inventoryRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        InventoryBatchRepositoryInterface $inventoryRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->productRepository = $productRepository;
    }

    public function importBatch(string $productId, string $batchName, string $rawData): InventoryBatch
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
            'active'
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
                throw new \Exception(__('messages.inventory_conflict'));
            }
        }

        $productId = $data['product_id'] ?? $batch->getProductId();
        $batchName = $data['batch_name'] ?? $batch->getBatchName();
        $keysPool = $data['keys_pool'] ?? $batch->getKeysPool();
        $status = $data['status'] ?? $batch->getStatus();

        $lines = preg_split('/\r\n|\r|\n/', $keysPool);
        $cleanKeys = array_filter(array_map('trim', $lines), fn($k) => !empty($k));
        $currentAvailableCount = count($cleanKeys);

        $soldCount = $batch->getSoldCount();
        $initialCount = $currentAvailableCount + $soldCount;

        $updatedBatch = new InventoryBatch(
            $batch->getId(),
            $productId,
            $batchName,
            implode("\n", $cleanKeys),
            $initialCount,
            $soldCount,
            $status,
            $batch->getCreatedAt(),
            $batch->getUpdatedAt()
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
}
