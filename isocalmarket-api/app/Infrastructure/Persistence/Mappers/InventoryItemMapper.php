<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\InventoryItem;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class InventoryItemMapper
{
    public static function toDomain(InventoryItemModel $model): InventoryItem
    {
        $decryptedData = '';
        try {
            $decryptedData = Crypt::decryptString($model->data);
        } catch (DecryptException $e) {
            // Fallback nếu dữ liệu chưa được mã hóa (để tương thích)
            $decryptedData = $model->data;
        }

        return new InventoryItem(
            $model->id,
            $model->product_id,
            $decryptedData,
            $model->status,
            $model->order_id
        );
    }

    public static function toPersistence(InventoryItem $entity): InventoryItemModel
    {
        $model = new InventoryItemModel();
        if ($entity->getId()) {
            $model = InventoryItemModel::find($entity->getId()) ?: $model;
        }

        $model->product_id = $entity->getProductId();
        $model->data = Crypt::encryptString($entity->getData());
        $model->status = $entity->getStatus();
        $model->order_id = $entity->getOrderId();

        return $model;
    }
}
