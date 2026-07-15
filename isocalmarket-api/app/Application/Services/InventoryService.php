<?php

namespace App\Application\Services;

use App\Domain\Entities\InventoryItem;
use App\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;

class InventoryService
{
    private InventoryItemRepositoryInterface $inventoryRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        InventoryItemRepositoryInterface $inventoryRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->productRepository = $productRepository;
    }

    public function importItems(string $productId, string $rawData): int
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new \Exception('Product not found.');
        }

        // Tách dữ liệu thô theo các dòng
        $lines = preg_split('/\r\n|\r|\n/', $rawData);
        $itemsToSave = [];
        $importedCount = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }

            $itemsToSave[] = new InventoryItem(
                null,
                $productId,
                $trimmed,
                'available'
            );
            $importedCount++;
        }

        if ($importedCount > 0) {
            $this->inventoryRepository->saveMany($itemsToSave);
        }

        return $importedCount;
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
