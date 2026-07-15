<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;

class OrderMapper
{
    public static function toDomain(OrderModel $model): Order
    {
        $items = [];
        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $productName = $itemModel->product ? $itemModel->product->name : 'Sản phẩm đã bị xóa';
                $items[] = new OrderItem(
                    $itemModel->id,
                    $itemModel->order_id,
                    $itemModel->product_id,
                    $productName,
                    $itemModel->price,
                    $itemModel->quantity
                );
            }
        }

        return new Order(
            $model->id,
            $model->user_id,
            $model->order_code,
            $model->total_price,
            $model->status,
            $items,
            new \DateTime($model->created_at->toDateTimeString())
        );
    }

    public static function toPersistence(Order $entity): OrderModel
    {
        $model = new OrderModel();
        if ($entity->getId()) {
            $model = OrderModel::find($entity->getId()) ?: $model;
        }

        $model->user_id = $entity->getUserId();
        $model->order_code = $entity->getOrderCode();
        $model->total_price = $entity->getTotalPrice();
        $model->status = $entity->getStatus();

        return $model;
    }
}
