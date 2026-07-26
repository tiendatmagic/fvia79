<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\InventoryItem;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;

class InventoryItemMapper
{
    public static function toDomain(InventoryItemModel $model): InventoryItem
    {
        $batchName = $model->relationLoaded('batch') && $model->batch ? $model->batch->batch_name : null;
        $productName = $model->relationLoaded('product') && $model->product ? $model->product->name : null;

        return new InventoryItem(
            $model->id,
            $model->batch_id,
            $model->product_id,
            $model->secret_data,
            $model->status,
            $model->order_id,
            $model->sold_at ? new \DateTimeImmutable($model->sold_at->toDateTimeString()) : null,
            $model->created_at ? new \DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            $model->updated_at ? new \DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
            $batchName,
            $productName
        );
    }

    public static function toPersistence(InventoryItem $entity): InventoryItemModel
    {
        $model = new InventoryItemModel();
        if ($entity->getId()) {
            $model = InventoryItemModel::find($entity->getId()) ?: $model;
        }

        $model->batch_id = $entity->getBatchId();
        $model->product_id = $entity->getProductId();
        $model->secret_data = $entity->getSecretData();
        $model->status = $entity->getStatus();
        $model->order_id = $entity->getOrderId();
        $model->sold_at = $entity->getSoldAt() ? $entity->getSoldAt()->format('Y-m-d H:i:s') : null;

        return $model;
    }
}
