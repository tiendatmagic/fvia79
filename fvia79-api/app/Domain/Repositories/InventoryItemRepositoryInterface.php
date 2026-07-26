<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\InventoryItem;

interface InventoryItemRepositoryInterface
{
    public function save(InventoryItem $item): InventoryItem;

    public function findById(string $id): ?InventoryItem;

    public function findAllPaginated(array $filters, int $page, int $limit): array;

    public function delete(string $id): bool;

    public function bulkInsert(array $items): bool;

    public function deleteAvailableByBatchId(string $batchId): int;

    public function popAvailableItems(string $productId, int $quantity, string $orderId): array;

    public function countAvailableByProductId(string $productId): int;

    public function countAvailableByBatchId(string $batchId): int;

    public function countSoldByBatchId(string $batchId): int;
}
