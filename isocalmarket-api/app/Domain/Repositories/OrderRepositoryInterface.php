<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): Order;
    public function findById(string $id): ?Order;
    /**
     * @return Order[]
     */
    public function findByUserId(string $userId): array;
    public function findByUserIdPaginated(string $userId, int $page, int $limit): array;
    /**
     * @return Order[]
     */
    public function findAll(): array;
    public function findAllPaginated(int $page, int $limit): array;
}
