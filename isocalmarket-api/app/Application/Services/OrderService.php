<?php

namespace App\Application\Services;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\InventoryItemRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ProductRepositoryInterface $productRepository;
    private InventoryItemRepositoryInterface $inventoryRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        InventoryItemRepositoryInterface $inventoryRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function createOrder(string $userId, array $itemsInput): Order
    {
        return DB::transaction(function () use ($userId, $itemsInput) {
            $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $totalPrice = 0.0;
            $orderItems = [];
            $itemsToUpdate = [];

            foreach ($itemsInput as $input) {
                $productId = $input['product_id'];
                $quantity = $input['quantity'];

                if ($quantity <= 0) {
                    throw new \Exception('Quantity must be greater than 0.');
                }
                $product = $this->productRepository->findById($productId);
                if (!$product) {
                    throw new \Exception("Product ID {$productId} not found.");
                }

                // Pessimistic Lock các items khả dụng để giữ hàng
                $availableItems = $this->inventoryRepository->findAvailableByProductIdForUpdate($productId, $quantity);

                if (count($availableItems) < $quantity) {
                    throw new \Exception("Sản phẩm '{$product->getName()}' không đủ số lượng tồn kho khả dụng (Yêu cầu: {$quantity}, Còn lại: " . count($availableItems) . ").");
                }
                $itemPrice = $product->getPrice();
                $totalPrice += $itemPrice * $quantity;

                $orderItems[] = new OrderItem(
                    null,
                    null,
                    $productId,
                    $product->getName(),
                    $itemPrice,
                    $quantity
                );
                foreach ($availableItems as $item) {
                    $itemsToUpdate[] = $item;
                }
            }

            $order = new Order(null, $userId, $orderCode, $totalPrice, 'paid', $orderItems);
            $savedOrder = $this->orderRepository->save($order);

            foreach ($itemsToUpdate as $item) {
                $item->markAsSold($savedOrder->getId());
                $this->inventoryRepository->save($item);
            }

            return $savedOrder;
        });
    }

    public function getOrderDetails(string $orderId, string $requestingUserId, string $role): ?array
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            return null;
        }

        // Kiểm tra quyền: chỉ Admin hoặc chính User mua hàng mới xem được
        if ($role !== 'admin' && $order->getUserId() !== $requestingUserId) {
            throw new \Exception('Forbidden. Access denied.');
        }

        $inventoryItems = [];
        if ($order->isPaid()) {
            $inventoryItems = $this->inventoryRepository->findByOrderId($orderId);
        }

        return [
            'order' => $order,
            'inventory_items' => $inventoryItems
        ];
    }

    public function getOrdersByUser(string $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }

    public function getOrdersByUserPaginated(string $userId, int $page, int $limit, ?string $status = null, ?string $search = null): array
    {
        return $this->orderRepository->findByUserIdPaginated($userId, $page, $limit, $status, $search);
    }

    public function getAllOrders(): array
    {
        return $this->orderRepository->findAll();
    }

    public function getAllOrdersPaginated(int $page, int $limit, ?string $status = null, ?string $search = null): array
    {
        return $this->orderRepository->findAllPaginated($page, $limit, $status, $search);
    }
}
