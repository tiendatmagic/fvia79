<?php

namespace App\Application\Services;

use App\Domain\Entities\InventoryBatch;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;

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

        return $this->inventoryRepository->save($batch);
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
                throw new \Exception('Dữ liệu lô hàng đã được cập nhật bởi một giao dịch khác. Vui lòng đóng modal và tải lại trang.');
            }
        }

        $productId = $data['product_id'] ?? $batch->getProductId();
        $batchName = $data['batch_name'] ?? $batch->getBatchName();
        $keysPool = $data['keys_pool'] ?? $batch->getKeysPool();
        $status = $data['status'] ?? $batch->getStatus();

        // Đếm lại số lượng khả dụng hiện có
        $lines = preg_split('/\r\n|\r|\n/', $keysPool);
        $cleanKeys = array_filter(array_map('trim', $lines), fn($k) => !empty($k));
        $currentAvailableCount = count($cleanKeys);

        // Khi admin sửa keys_pool, sold_count (số lượng đã bán thực tế) được giữ nguyên.
        // initial_count (tổng số lượng ban đầu) sẽ được tính lại bằng: số lượng khả dụng hiện tại + số lượng đã bán.
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

        return $this->inventoryRepository->save($updatedBatch);
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
        return $this->inventoryRepository->delete($id);
    }
}
