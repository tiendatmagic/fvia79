<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Order;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use App\Infrastructure\Persistence\Mappers\OrderMapper;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function save(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $model = OrderMapper::toPersistence($order);
            $model->save();

            // Xóa các items cũ nếu có (trong trường hợp update)
            if ($order->getId()) {
                OrderItemModel::where('order_id', $model->id)->delete();
            }
            foreach ($order->getItems() as $item) {
                $itemModel = new OrderItemModel();
                $itemModel->order_id = $model->id;
                $itemModel->product_id = $item->getProductId();
                $itemModel->product_name = $item->getProductName();
                $itemModel->price = $item->getPrice();
                $itemModel->quantity = $item->getQuantity();
                $itemModel->delivered_keys = $item->getDeliveredKeys();
                $itemModel->save();
            }

            $model->load(['items.product']);
            return OrderMapper::toDomain($model);
        });
    }

    public function findById(string $id): ?Order
    {
        $model = OrderModel::with(['items.product'])->find($id);
        return $model ? OrderMapper::toDomain($model) : null;
    }

    public function findByUserId(string $userId): array
    {
        $models = OrderModel::with(['items.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($m) => OrderMapper::toDomain($m))->toArray();
    }

    public function findByUserIdPaginated(string $userId, int $page, int $limit, ?string $status = null, ?string $search = null): array
    {
        $query = OrderModel::where('user_id', $userId);
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%");
            });
        }
        $total = $query->count();
        $models = $query->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'items' => $models->map(fn($m) => OrderMapper::toDomain($m))->toArray(),
            'total' => $total
        ];
    }

    public function findAll(): array
    {
        $models = OrderModel::with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($m) => OrderMapper::toDomain($m))->toArray();
    }

    public function findAllPaginated(int $page, int $limit, ?string $status = null, ?string $search = null): array
    {
        $query = OrderModel::query();
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }
        $total = $query->count();
        $models = $query->with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'items' => $models->map(fn($m) => OrderMapper::toDomain($m))->toArray(),
            'total' => $total
        ];
    }
}
