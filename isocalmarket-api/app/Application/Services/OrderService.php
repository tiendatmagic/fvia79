<?php

namespace App\Application\Services;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class OrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ProductRepositoryInterface $productRepository;
    private InventoryBatchRepositoryInterface $inventoryRepository;
    private \App\Domain\Repositories\UserRepositoryInterface $userRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        InventoryBatchRepositoryInterface $inventoryRepository,
        \App\Domain\Repositories\UserRepositoryInterface $userRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->userRepository = $userRepository;
    }

    public function createOrder(string $userId, array $itemsInput): Order
    {
        return DB::transaction(function () use ($userId, $itemsInput) {
            $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $totalPrice = 0.0;
            $orderItems = [];
            $batchesToSave = [];

            foreach ($itemsInput as $input) {
                $productId = $input['product_id'];
                $quantity = $input['quantity'];

                if ($quantity <= 0) {
                    throw new \Exception('Quantity must be greater than 0.');
                }
                $product = $this->productRepository->findById($productId);
                if (!$product || $product->isDeleted()) {
                    throw new \Exception("Product is not available or has been discontinued.");
                }

                $availableCount = $this->inventoryRepository->countAvailableByProductId($productId);
                if ($availableCount < $quantity) {
                    throw new \Exception("Product '{$product->getName()}' does not have enough stock available (Requested: {$quantity}, Available: {$availableCount}).");
                }

                $activeBatches = $this->inventoryRepository->findActiveBatchesByProductId($productId, true);

                $deliveredKeys = [];
                $neededQuantity = $quantity;

                foreach ($activeBatches as $batch) {
                    if ($neededQuantity <= 0) {
                        break;
                    }
                    $batchAvailable = $batch->getAvailableCount();
                    if ($batchAvailable === 0) {
                        continue;
                    }

                    $takeCount = min($neededQuantity, $batchAvailable);
                    $popped = $batch->popKeys($takeCount);
                    $deliveredKeys = array_merge($deliveredKeys, $popped);
                    $neededQuantity -= $takeCount;

                    $batchesToSave[] = $batch;
                }

                if (count($deliveredKeys) < $quantity) {
                    throw new \Exception("An error occurred: Could not retrieve enough digital resources for product '{$product->getName()}'.");
                }

                $itemPrice = $product->getPrice();
                $totalPrice += $itemPrice * $quantity;

                $orderItems[] = new OrderItem(
                    null,
                    null,
                    $productId,
                    $product->getName(),
                    $itemPrice,
                    $quantity,
                    $deliveredKeys
                );
            }

            // Kiểm tra và trừ tiền từ số dư của người dùng
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new \Exception('User account not found.');
            }

            if ($user->getBalance() < $totalPrice) {
                throw new \Exception("Insufficient balance to pay for the order (Required: " . number_format($totalPrice) . " VND, Current: " . number_format($user->getBalance()) . " VND).");
            }

            $user->setBalance($user->getBalance() - $totalPrice);
            $this->userRepository->save($user);

            $order = new Order(null, $userId, $orderCode, $totalPrice, 'paid', $orderItems);
            $savedOrder = $this->orderRepository->save($order);

            foreach ($batchesToSave as $batch) {
                $this->inventoryRepository->save($batch);
            }

            Cache::forget('products_active_storefront');

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

        return [
            'order' => $order,
            'inventory_items' => []
        ];
    }

    public function getOrdersByUser(string $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }

    public function getOrdersByUserPaginated(string $userId, int $page, int $limit, ?string $status = null, ?string $search = null, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->orderRepository->findByUserIdPaginated($userId, $page, $limit, $status, $search, $startDate, $endDate);
    }

    public function getAllOrders(): array
    {
        return $this->orderRepository->findAll();
    }

    public function getAllOrdersPaginated(int $page, int $limit, ?string $status = null, ?string $search = null, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->orderRepository->findAllPaginated($page, $limit, $status, $search, $startDate, $endDate);
    }
}
