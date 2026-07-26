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
                $productName = $itemModel->product_name ?: ($itemModel->product ? $itemModel->product->name : 'Deleted product');
                $items[] = new OrderItem(
                    $itemModel->id,
                    $itemModel->order_id,
                    $itemModel->product_id,
                    $productName,
                    $itemModel->price,
                    $itemModel->quantity,
                    $itemModel->delivered_keys,
                    $itemModel->cost_price
                );
            }
        }

        $userEmail = $model->relationLoaded('user') && $model->user ? $model->user->email : null;
        $userName = $model->relationLoaded('user') && $model->user ? ($model->user->name ?: $model->user->username) : null;
        $userUid = $model->relationLoaded('user') && $model->user ? $model->user->uid : null;

        return new Order(
            $model->id,
            $model->user_id,
            $model->order_code,
            $model->total_price,
            $model->status,
            $items,
            new \DateTime($model->created_at->toDateTimeString()),
            $userEmail,
            $model->coupon_code,
            (float) $model->discount_amount,
            $model->coupon_id,
            $userName,
            $userUid,
            (float) ($model->tier_discount_amount ?? 0),
            (float) ($model->coupon_discount_amount ?? 0)
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
        $model->coupon_code = $entity->getCouponCode();
        $model->discount_amount = $entity->getDiscountAmount();
        $model->coupon_id = $entity->getCouponId();
        $model->tier_discount_amount = $entity->getTierDiscountAmount();
        $model->coupon_discount_amount = $entity->getCouponDiscountAmount();

        return $model;
    }
}
