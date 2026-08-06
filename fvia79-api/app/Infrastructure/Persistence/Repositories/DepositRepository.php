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
        $model->load('user');

        return DepositMapper::toDomain($model);
    }

    public function find(string $id): ?Deposit
    {
        $model = DepositModel::with('user')->find($id);

        return $model ? DepositMapper::toDomain($model) : null;
    }

    public function findByCode(string $code): ?Deposit
    {
        $model = DepositModel::with('user')->where('code', $code)->first();

        return $model ? DepositMapper::toDomain($model) : null;
    }

    public function findByUserIdPaginated(string $userId, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);
        $paginator = DepositModel::with('user:id,email,name,username,uid')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return [
            'items' => array_map(fn ($m) => DepositMapper::toDomain($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'limit' => $limit,
        ];
    }

    public function findPendingByAmountToPay(float $amountToPay): ?Deposit
    {
        $model = DepositModel::with('user')->where('status', 'pending_payment')
            ->where('amount_to_pay', $amountToPay)
            ->first();

        return $model ? DepositMapper::toDomain($model) : null;
    }

    public function isTxHashUsed(string $txHash): bool
    {
        return DepositModel::where('tx_hash', $txHash)
            ->whereNotNull('tx_hash')
            ->exists();
    }

    public function findAllPaginated(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $paymentMethod = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $page = max(1, $page);
        $limit = max(1, $limit);
        $query = DepositModel::query()->with('user');

        if ($status) {
            $query->where('status', $status);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate.' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate.' 23:59:59');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('tx_hash', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('uid', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return [
            'items' => array_map(fn ($m) => DepositMapper::toDomain($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'limit' => $limit,
        ];
    }
}
