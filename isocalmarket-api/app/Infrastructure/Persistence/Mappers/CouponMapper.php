<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\Coupon;
use App\Infrastructure\Persistence\Eloquent\CouponModel;

class CouponMapper
{
    public static function toDomain(CouponModel $model): Coupon
    {
        return new Coupon(
            $model->id,
            $model->code,
            $model->type,
            (float) $model->value,
            (float) $model->min_spend,
            $model->start_date,
            $model->end_date,
            (int) $model->usage_limit,
            (int) $model->used_count,
            $model->created_at
        );
    }

    public static function toPersistence(Coupon $entity): CouponModel
    {
        $model = new CouponModel();
        if ($entity->getId()) {
            $model = CouponModel::find($entity->getId()) ?: $model;
        }

        $model->code = $entity->getCode();
        $model->type = $entity->getType();
        $model->value = $entity->getValue();
        $model->min_spend = $entity->getMinSpend();
        $model->start_date = $entity->getStartDate();
        $model->end_date = $entity->getEndDate();
        $model->usage_limit = $entity->getUsageLimit();
        $model->used_count = $entity->getUsedCount();

        return $model;
    }
}
