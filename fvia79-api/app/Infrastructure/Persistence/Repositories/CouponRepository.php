<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Coupon;
use App\Domain\Repositories\CouponRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\CouponModel;
use App\Infrastructure\Persistence\Mappers\CouponMapper;

class CouponRepository implements CouponRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    public function save(Coupon $coupon): Coupon
    {
        $model = CouponMapper::toPersistence($coupon);
        $model->save();
        return CouponMapper::toDomain($model);
    }

    public function findById(string $id): ?Coupon
    {
        $model = CouponModel::find($id);
        if (!$model) return null;
        return CouponMapper::toDomain($model);
    }

    public function findByCode(string $code): ?Coupon
    {
        $model = CouponModel::withTrashed()->where('code', $code)->first();
        if (!$model) return null;
        return CouponMapper::toDomain($model);
    }

    public function findAll(): array
    {
        $models = CouponModel::orderBy('created_at', 'desc')->get();
        $coupons = [];
        foreach ($models as $model) {
            $coupons[] = CouponMapper::toDomain($model);
        }
        return $coupons;
    }

    public function findAllPaginated(
        int $page,
        int $limit,
        ?string $search = null,
        ?string $status = null,
        ?string $type = null
    ): array {
        $now = now()->toDateTimeString();

        if ($status === 'deleted') {
            $query = CouponModel::onlyTrashed();
        } else if ($status === 'all') {
            $query = CouponModel::withTrashed();
        } else {
            $query = CouponModel::query();
        }

        if ($status === 'active') {
            $query->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->where(function ($q) {
                    $q->where('usage_limit', 0)
                        ->orWhereRaw('used_count < usage_limit');
                });
        } else if ($status === 'upcoming') {
            $query->where('start_date', '>', $now);
        } else if ($status === 'expired') {
            $query->where(function ($q) use ($now) {
                $q->where('end_date', '<', $now)
                    ->orWhere(function ($sub) {
                        $sub->where('usage_limit', '>', 0)
                            ->whereRaw('used_count >= usage_limit');
                    });
            });
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $this->applyAccentInsensitiveSearch($q, 'code', $search);
            });
        }

        $total = $query->count();
        $models = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $coupons = [];
        foreach ($models as $model) {
            $coupons[] = CouponMapper::toDomain($model);
        }

        return [
            'items' => $coupons,
            'total' => $total
        ];
    }

    public function delete(string $id): bool
    {
        $model = CouponModel::find($id);
        if ($model) {
            return (bool) $model->delete();
        }
        return false;
    }

    public function restore(string $id): bool
    {
        $model = CouponModel::withTrashed()->find($id);
        if ($model) {
            return (bool) $model->restore();
        }
        return false;
    }
}
