<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\InventoryItem;

interface InventoryItemRepositoryInterface
{
    /**
     * @return InventoryItem[]
     */
    public function findAvailableByProductId(string $productId, int $limit): array;
    /**
     * @return InventoryItem[]
     */
    public function findAvailableByProductIdForUpdate(string $productId, int $limit): array;
    public function countAvailableByProductId(string $productId): int;
    public function save(InventoryItem $item): InventoryItem;
    /**
     * @param InventoryItem[] $items
     * @return bool
     */
    public function saveMany(array $items): bool;
    /**
     * @return InventoryItem[]
     */
    public function findByOrderId(string $orderId): array;
    /**
     * @return InventoryItem[]
     */
    public function findAll(array $filters): array;
    public function findAllPaginated(array $filters, int $page, int $limit): array;
    public function findById(string $id): ?InventoryItem;
    public function delete(string $id): bool;
}
