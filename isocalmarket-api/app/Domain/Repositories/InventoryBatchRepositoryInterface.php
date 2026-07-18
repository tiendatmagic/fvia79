<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\InventoryBatch;

interface InventoryBatchRepositoryInterface
{
    /**
     * @param string $productId
     * @param bool $lock
     * @return InventoryBatch[]
     */
    public function findActiveBatchesByProductId(string $productId, bool $lock = false): array;

    public function countAvailableByProductId(string $productId): int;

    public function save(InventoryBatch $batch): InventoryBatch;

        public function findAll(array $filters): array;

    public function findAllPaginated(array $filters, int $page, int $limit): array;

    public function findById(string $id): ?InventoryBatch;

    public function delete(string $id): bool;
}
