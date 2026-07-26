<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Deposit;

interface DepositRepositoryInterface
{
    public function save(Deposit $deposit): Deposit;

    public function find(string $id): ?Deposit;

    public function findByCode(string $code): ?Deposit;

    /** Return a user's deposits with pagination. */
    public function findByUserIdPaginated(string $userId, int $page = 1, int $limit = 10): array;

    /** Find a pending deposit by its unique fractional amount. */
    public function findPendingByAmountToPay(float $amountToPay): ?Deposit;

    /** Determine whether a transaction hash has already been reconciled. */
    public function isTxHashUsed(string $txHash): bool;

    /** Return all deposits with admin filters and pagination. */
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
