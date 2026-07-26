<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\InventoryBatch;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;

class InventoryBatchMapper
{
    public static function toDomain(InventoryBatchModel $model): InventoryBatch
    {
        return new InventoryBatch(
            $model->id,
            $model->product_id,
            $model->batch_name,
            $model->keys_pool,
            $model->initial_count,
            $model->sold_count,
            $model->status,
            $model->created_at,
            $model->updated_at,
            $model->cost_price
        );
    }

    public static function toPersistence(InventoryBatch $entity): InventoryBatchModel
    {
        $model = new InventoryBatchModel();
        if ($entity->getId()) {
            $model = InventoryBatchModel::find($entity->getId()) ?: $model;
        }

        $model->product_id = $entity->getProductId();
        $model->batch_name = $entity->getBatchName();
        $model->cost_price = $entity->getCostPrice();
        $model->initial_count = $entity->getInitialCount();
        $model->sold_count = $entity->getSoldCount();
        $model->status = $entity->getStatus();

        return $model;
    }
}
