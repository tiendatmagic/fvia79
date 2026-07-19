<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Deposit;

interface DepositRepositoryInterface
{
    public function save(Deposit $deposit): Deposit;

    public function find(string $id): ?Deposit;

    public function findByCode(string $code): ?Deposit;

    /**
     * Lấy danh sách lệnh nạp tiền của user có phân trang
     */
    public function findByUserIdPaginated(string $userId, int $page = 1, int $limit = 10): array;

    /**
     * Tìm lệnh nạp đang chờ thanh toán (pending_payment) khớp với số lẻ thực nhận
     */
    public function findPendingByAmountToPay(float $amountToPay): ?Deposit;

    /**
     * Kiểm tra xem TxHash này đã được sử dụng đối soát chưa
     */
    public function isTxHashUsed(string $txHash): bool;

    /**
     * Lấy danh sách toàn bộ lệnh nạp tiền có phân trang, tìm kiếm và lọc dành cho admin
     */
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
