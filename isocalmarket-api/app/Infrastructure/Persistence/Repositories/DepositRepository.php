<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Deposit;
use App\Domain\Repositories\DepositRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use App\Infrastructure\Persistence\Mappers\DepositMapper;

class DepositRepository implements DepositRepositoryInterface
{
    public function save(Deposit $deposit): Deposit
    {
        $model = DepositMapper::toPersistence($deposit);
        $model->save();
        return DepositMapper::toDomain($model);
    }

    public function find(string $id): ?Deposit
    {
        $model = DepositModel::find($id);
        return $model ? DepositMapper::toDomain($model) : null;
    }

    public function findByCode(string $code): ?Deposit
    {
        $model = DepositModel::where('code', $code)->first();
        return $model ? DepositMapper::toDomain($model) : null;
    }

    public function findByUserIdPaginated(string $userId, int $page = 1, int $limit = 10): array
    {
        $paginator = DepositModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return [
            'items' => array_map(fn($m) => DepositMapper::toDomain($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'limit' => $limit
        ];
    }

    public function findPendingByAmountToPay(float $amountToPay): ?Deposit
    {
        // Cho phép dung sai làm tròn nhỏ nếu cần, ở đây so khớp trực tiếp tới 4 số thập phân
        $model = DepositModel::where('status', 'pending_payment')
            ->where('amount_to_pay', $amountToPay)
            ->first();

        return $model ? DepositMapper::toDomain($model) : null;
    }

    public function isTxHashUsed(string $txHash): bool
    {
        return DepositModel::where('tx_hash', $txHash)
            ->where('status', 'completed')
            ->exists();
    }
}
