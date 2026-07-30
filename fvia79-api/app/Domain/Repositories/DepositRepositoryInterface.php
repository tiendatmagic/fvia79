<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Deposit;

interface DepositRepositoryInterface
{
    public function save(Deposit $deposit): Deposit;

    public function find(string $id): ?Deposit;

    public function findByCode(string $code): ?Deposit;

    public function findByUserIdPaginated(string $userId, int $page = 1, int $limit = 10): array;

    public function findPendingByAmountToPay(float $amountToPay): ?Deposit;

    public function isTxHashUsed(string $txHash): bool;

    public function findAllPaginated(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $paymentMethod = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array;
}
