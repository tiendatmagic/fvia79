<?php

namespace App\Application\Services;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Exceptions\OrderException;
use App\Domain\Repositories\CouponRepositoryInterface;
use App\Domain\Repositories\InventoryBatchRepositoryInterface;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\CouponModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierPurchaseModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Integrations\Suppliers\SupplierConnectorFactory;
use App\Integrations\Suppliers\SupplierOrderPendingException;
use App\Integrations\Suppliers\SupplierOutOfStockException;
use App\Integrations\Suppliers\SupplierPurchaseRejectedException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OrderService
{
    private OrderRepositoryInterface $orderRepository;

    private ProductRepositoryInterface $productRepository;

    private InventoryBatchRepositoryInterface $inventoryRepository;

    private UserRepositoryInterface $userRepository;

    private CouponRepositoryInterface $couponRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        InventoryBatchRepositoryInterface $inventoryRepository,
        UserRepositoryInterface $userRepository,
        CouponRepositoryInterface $couponRepository,
        private readonly SupplierConnectorFactory $supplierConnectorFactory,
        private readonly DepositService $depositService,
        private readonly ?TelegramNotificationService $telegramService = null,
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->userRepository = $userRepository;
        $this->couponRepository = $couponRepository;
    }

    public function createOrder(
        string $userId,
        array $itemsInput,
        ?string $couponCode = null,
        ?string $idempotencyKey = null,
    ): Order {
        if ($idempotencyKey !== null) {
            $existing = OrderModel::with('items')
                ->where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->where('status', '!=', 'cancelled')
                ->first();
            if ($existing) {
                $this->assertIdempotentRequestMatches($existing, $itemsInput, $couponCode);
                $this->assertIdempotentOrderCanBeReturned($existing);

                return $this->orderRepository->findById($existing->id);
            }
        }

        try {
            $preparedOrder = DB::transaction(function () use ($userId, $itemsInput, $couponCode, $idempotencyKey) {
                $orderCode = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(6));
                $totalPrice = 0.0;
                $orderItems = [];

                foreach ($itemsInput as $input) {
                    $productId = $input['product_id'];
                    $quantity = $input['quantity'];

                    if ($quantity <= 0) {
                        throw new OrderException('Quantity must be greater than 0.');
                    }
                    $product = $this->productRepository->findById($productId);
                    if (! $product || $product->isDeleted() || ! $product->isActive()) {
                        throw new OrderException('Product is not available or has been discontinued.');
                    }

                    $availableCount = $product->getAvailableCount();
                    if ($availableCount < $quantity) {
                        throw new OrderException("Product '{$product->getName()}' does not have enough stock available (Requested: {$quantity}, Available: {$availableCount}).");
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
                        [],
                        0.0
                    );
                }

                $user = $this->userRepository->findByIdForUpdate($userId);
                if (! $user) {
                    throw new OrderException('User account not found.');
                }

                $tierDiscountAmount = 0.0;
                $featureEnabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
                $tiersEnabled = app(SystemSettingService::class)->getSetting('membership_tiers_enabled', '1');
                if ($featureEnabled && filter_var($tiersEnabled, FILTER_VALIDATE_BOOLEAN)) {
                    $membershipTier = $user->getMembershipTier();
                    if ($membershipTier && ! empty($membershipTier['discount_percent']) && (float) $membershipTier['discount_percent'] > 0) {
                        $tierPercent = (float) $membershipTier['discount_percent'];
                        $tierDiscountAmount = round($totalPrice * ($tierPercent / 100), 4);
                    }
                }

                $couponDiscountAmount = 0.0;
                $couponId = null;
                $appliedCouponCode = null;

                $couponsEnvFeatureEnabled = filter_var(config('app.feature_coupons_enabled', true), FILTER_VALIDATE_BOOLEAN);
                $couponsEnabled = $couponsEnvFeatureEnabled
                    ? filter_var(app(SystemSettingService::class)->getSetting('coupons_enabled', '1'), FILTER_VALIDATE_BOOLEAN)
                    : false;

                if (! $couponsEnabled) {
                    $couponCode = null;
                }

                if ($couponCode !== null && trim($couponCode) !== '') {
                    $coupon = $this->couponRepository->findByCode(trim($couponCode));
                    if (! $coupon) {
                        throw new OrderException('Coupon code is invalid or does not exist.');
                    }

                    if (! $coupon->isValidForCart($totalPrice)) {
                        if (! $coupon->isActive()) {
                            throw new OrderException('This coupon is not active yet.');
                        }
                        if ($coupon->isExpired()) {
                            throw new OrderException('This coupon has expired.');
                        }
                        if ($coupon->isLimitReached()) {
                            throw new OrderException('This coupon has reached its usage limit.');
                        }
                        if ($totalPrice < $coupon->getMinSpend()) {
                            throw new OrderException('Cart total does not meet the minimum spend requirement for this coupon.');
                        }
                        throw new OrderException('Coupon is not applicable to this order.');
                    }

                    $couponDiscountAmount = $coupon->calculateDiscount($totalPrice);
                    $couponId = $coupon->getId();
                    $appliedCouponCode = $coupon->getCode();

                    if (! $this->couponRepository->reserveUsage($coupon->getId())) {
                        throw new OrderException('This coupon has reached its usage limit.');
                    }
                }

                $discountAmount = $tierDiscountAmount + $couponDiscountAmount;
                $finalPrice = max(0.0, $totalPrice - $discountAmount);

                if ($user->getBalance() < $finalPrice) {
                    throw new OrderException('Insufficient balance to pay for the order (Required: '.number_format($finalPrice, 4).' USD, Current: '.number_format($user->getBalance(), 4).' USD).');
                }

                $user->setBalance($user->getBalance() - $finalPrice);
                $this->userRepository->save($user);

                $order = new Order(
                    null,
                    $userId,
                    $orderCode,
                    $finalPrice,
                    'pending',
                    $orderItems,
                    null,
                    null,
                    $appliedCouponCode,
                    $discountAmount,
                    $couponId,
                    null,
                    null,
                    $tierDiscountAmount,
                    $couponDiscountAmount
                );
                $savedOrder = $this->orderRepository->save($order);
                if ($idempotencyKey !== null) {
                    OrderModel::whereKey($savedOrder->getId())->update(['idempotency_key' => $idempotencyKey]);
                }

                return $savedOrder;
            });
        } catch (QueryException $exception) {
            $existing = $idempotencyKey === null
                ? null
                : OrderModel::with('items')
                    ->where('user_id', $userId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->where('status', '!=', 'cancelled')
                    ->first();
            if (! $existing) {
                throw $exception;
            }
            $this->assertIdempotentRequestMatches($existing, $itemsInput, $couponCode);
            $this->assertIdempotentOrderCanBeReturned($existing);

            return $this->orderRepository->findById($existing->id);
        }

        try {
            $completedOrder = $this->fulfillPreparedOrder($preparedOrder);
            StorefrontActivityCache::invalidatePurchases();

            try {
                $userModel = UserModel::find($userId);
                $service = $this->telegramService ?? app(TelegramNotificationService::class);
                if ($userModel) {
                    $service->notifyOrderSuccess($completedOrder, $userModel);
                }
            } catch (Throwable $e) {
                Log::error('Telegram order notification error: '.$e->getMessage());
            }

            return $completedOrder;
        } catch (OrderException $exception) {
            $hasUnknownSupplierPurchase = SupplierPurchaseModel::where('order_id', $preparedOrder->getId())
                ->where('status', 'unknown')
                ->exists();
            if ($hasUnknownSupplierPurchase) {
                return $this->orderRepository->findById($preparedOrder->getId());
            }

            throw $exception;
        }
    }

    private function assertIdempotentRequestMatches(
        OrderModel $existing,
        array $itemsInput,
        ?string $couponCode,
    ): void {
        $requestedItems = array_map(
            fn (array $item): array => [
                'product_id' => (string) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ],
            $itemsInput,
        );
        $existingItems = $existing->items
            ->map(fn (OrderItemModel $item): array => [
                'product_id' => (string) $item->product_id,
                'quantity' => (int) $item->quantity,
            ])
            ->all();
        $sortItems = static function (array &$items): void {
            usort($items, static fn (array $left, array $right): int => [
                $left['product_id'],
                $left['quantity'],
            ] <=> [
                $right['product_id'],
                $right['quantity'],
            ]);
        };
        $sortItems($requestedItems);
        $sortItems($existingItems);
        $requestedCoupon = $couponCode === null || trim($couponCode) === ''
            ? null
            : strtoupper(trim($couponCode));
        $existingCoupon = $existing->coupon_code === null || trim($existing->coupon_code) === ''
            ? null
            : strtoupper(trim($existing->coupon_code));

        if ($requestedItems !== $existingItems || $requestedCoupon !== $existingCoupon) {
            throw new OrderException(
                'The idempotency key has already been used for a different order request.',
            );
        }
    }

    private function assertIdempotentOrderCanBeReturned(OrderModel $existing): void
    {
        if ($existing->status === 'cancelled') {
            throw new OrderException(
                'The previous order for this idempotency key was cancelled. Use a new key for a new purchase attempt.',
            );
        }
    }

    private function fulfillPreparedOrder(Order $order): Order
    {
        $plans = [];
        $hasSupplierSuccess = false;

        try {
            $orderItems = $order->getItems();
            $products = ProductModel::with('supplier')
                ->whereKey(array_values(array_unique(array_map(
                    fn (OrderItem $item) => $item->getProductId(),
                    $orderItems,
                ))))
                ->get()
                ->keyBy('id');
            $purchases = SupplierPurchaseModel::query()
                ->whereIn('order_item_id', array_values(array_filter(array_map(
                    fn (OrderItem $item) => $item->getId(),
                    $orderItems,
                ))))
                ->whereIn('status', ['succeeded', 'unknown'])
                ->orderByDesc('created_at')
                ->get()
                ->unique('order_item_id')
                ->keyBy('order_item_id');

            foreach ($orderItems as $item) {
                $product = $products->get($item->getProductId())
                    ?? ProductModel::with('supplier')->findOrFail($item->getProductId());
                $supplierQuantity = 0;
                $supplierOrderId = null;
                $supplierCode = null;
                $resources = [];
                $supplierCost = 0.0;
                $purchase = $purchases->get($item->getId());
                $wantedFromSupplier = $purchase
                    ? $purchase->quantity
                    : (($product->supplier_id
                    && $product->external_id
                    && $product->supplier?->enabled
                    && $product->supplier?->products_enabled
                    ? $this->reserveSupplierStock($product->id, $item->getQuantity())
                    : 0));

                if ($wantedFromSupplier > 0) {
                    if (! $purchase) {
                        $purchase = SupplierPurchaseModel::create([
                            'order_id' => $order->getId(),
                            'order_item_id' => $item->getId(),
                            'supplier_id' => $product->supplier_id,
                            'product_id' => $product->id,
                            'quantity' => $wantedFromSupplier,
                            'status' => 'pending',
                        ]);
                    } elseif ($purchase->status === 'unknown' && ! $purchase->supplier_order_id) {
                        throw new OrderException(
                            'The supplier purchase result is uncertain. The order requires manual reconciliation.',
                        );
                    }

                    try {
                        $connector = $this->supplierConnectorFactory->makeOrderConnector($product->supplier);
                        $result = $purchase->supplier_order_id
                            ? $connector->fetchOrder($product->supplier, $purchase->supplier_order_id)
                            : $connector->purchase(
                                $product->supplier,
                                (string) $product->external_id,
                                $wantedFromSupplier,
                            );
                        $resources = array_slice($result['resources'], 0, $wantedFromSupplier);
                        if (count($resources) !== $wantedFromSupplier) {
                            throw new SupplierOrderPendingException(
                                $result['order_id'],
                                $result['raw'],
                            );
                        }

                        $supplierQuantity = $wantedFromSupplier;
                        $supplierOrderId = $result['order_id'];
                        $supplierCode = $product->supplier->code;
                        $purchase->update([
                            'supplier_order_id' => $supplierOrderId,
                            'status' => 'succeeded',
                            'response_payload' => $result['raw'],
                            'error' => null,
                        ]);
                        $hasSupplierSuccess = true;
                        try {
                            $rate = $this->depositService->getUsdtToVndRate();
                            $supplierCost = $rate > 0
                                ? ((float) $product->supplier_price_vnd / $rate) * $supplierQuantity
                                : 0.0;
                        } catch (Throwable) {
                            $supplierCost = 0.0;
                        }
                    } catch (SupplierOutOfStockException $exception) {
                        $purchase->update([
                            'status' => 'failed',
                            'error' => Str::limit($exception->getMessage(), 2000),
                        ]);
                        $product->update(['supplier_stock' => 0]);
                    } catch (SupplierPurchaseRejectedException $exception) {
                        $purchase->update([
                            'status' => 'failed',
                            'error' => Str::limit($exception->getMessage(), 2000),
                        ]);
                        ProductModel::whereKey($product->id)->increment(
                            'supplier_stock',
                            $wantedFromSupplier,
                        );
                        throw new OrderException(
                            'The supplier rejected the purchase. The order has been cancelled and refunded.',
                            previous: $exception,
                        );
                    } catch (SupplierOrderPendingException $exception) {
                        $purchase->update([
                            'supplier_order_id' => $exception->supplierOrderId,
                            'status' => 'unknown',
                            'response_payload' => $exception->responsePayload,
                            'error' => Str::limit($exception->getMessage(), 2000),
                        ]);
                        throw new OrderException(
                            'The supplier order is pending reconciliation and will not be purchased again automatically.',
                            previous: $exception,
                        );
                    } catch (Throwable $exception) {
                        $purchase->update([
                            'status' => 'unknown',
                            'error' => Str::limit($exception->getMessage(), 2000),
                        ]);
                        throw new OrderException(
                            'The supplier purchase result is uncertain. The order is pending reconciliation and will not be purchased again automatically.',
                            previous: $exception,
                        );
                    }
                }

                $plans[] = [
                    'item' => $item,
                    'product' => $product,
                    'supplier_quantity' => $supplierQuantity,
                    'local_quantity' => $item->getQuantity() - $supplierQuantity,
                    'supplier_order_id' => $supplierOrderId,
                    'supplier_code' => $supplierCode,
                    'resources' => $resources,
                    'supplier_cost' => $supplierCost,
                ];
            }

            DB::transaction(function () use ($order, $plans): void {
                $orderModel = OrderModel::whereKey($order->getId())->lockForUpdate()->firstOrFail();
                if ($orderModel->status !== 'pending') {
                    return;
                }

                foreach ($plans as $plan) {
                    $localResources = [];
                    $localCost = 0.0;
                    $needed = $plan['local_quantity'];
                    $batches = $this->inventoryRepository
                        ->findActiveBatchesByProductId($plan['product']->id, true);

                    foreach ($batches as $batch) {
                        if ($needed <= 0) {
                            break;
                        }
                        $take = min($needed, $batch->getAvailableCount());
                        if ($take <= 0) {
                            continue;
                        }
                        $popped = $batch->popKeys($take);
                        $localResources = array_merge($localResources, $popped);
                        $localCost += ($batch->getCostPrice() ?? 0.0) * count($popped);
                        $needed -= count($popped);
                        $this->inventoryRepository->save($batch);
                    }

                    if ($needed > 0) {
                        throw new OrderException(
                            "Product '{$plan['product']->name}' does not have enough local fallback stock.",
                        );
                    }

                    $itemModel = OrderItemModel::whereKey($plan['item']->getId())->lockForUpdate()->firstOrFail();
                    $itemModel->delivered_keys = array_merge($plan['resources'], $localResources);
                    $itemModel->supplier_quantity = $plan['supplier_quantity'];
                    $itemModel->local_quantity = $plan['local_quantity'];
                    $itemModel->supplier_order_id = $plan['supplier_order_id'];
                    $itemModel->supplier_code = $plan['supplier_code'];
                    $itemModel->cost_price = $itemModel->quantity > 0
                        ? ($plan['supplier_cost'] + $localCost) / $itemModel->quantity
                        : 0.0;
                    $itemModel->save();

                }

                $orderModel->status = 'paid';
                $orderModel->save();
            });
        } catch (Throwable $exception) {
            if (! $hasSupplierSuccess
                && ! SupplierPurchaseModel::where('order_id', $order->getId())->where('status', 'unknown')->exists()) {
                $this->cancelAndRefund($order->getId());
            }
            Cache::forget('products_active_storefront');

            if ($exception instanceof OrderException) {
                throw $exception;
            }

            throw new OrderException('Order fulfillment failed.', previous: $exception);
        }

        Cache::forget('products_active_storefront');

        return $this->orderRepository->findById($order->getId());
    }

    private function reserveSupplierStock(string $productId, int $requestedQuantity): int
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $available = max(0, (int) ProductModel::whereKey($productId)->value('supplier_stock'));
            $quantity = min($requestedQuantity, $available);
            if ($quantity === 0) {
                return 0;
            }

            $reserved = ProductModel::whereKey($productId)
                ->where('supplier_stock', '>=', $quantity)
                ->decrement('supplier_stock', $quantity);
            if ($reserved === 1) {
                return $quantity;
            }
        }

        return 0;
    }

    public function reconcilePendingSupplierOrders(): array
    {
        $results = [];
        $orderIds = SupplierPurchaseModel::whereIn('status', ['unknown', 'succeeded'])
            ->whereNotNull('supplier_order_id')
            ->whereIn('order_id', OrderModel::select('id')->where('status', 'pending'))
            ->pluck('order_id')
            ->unique();

        foreach ($orderIds as $orderId) {
            $order = $this->orderRepository->findById($orderId);
            if (! $order || ! $order->isPending()) {
                continue;
            }

            try {
                $results[$orderId] = $this->fulfillPreparedOrder($order)->getStatus();
            } catch (Throwable) {
                $results[$orderId] = 'pending';
            }
        }

        return $results;
    }

    private function cancelAndRefund(string $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $order = OrderModel::whereKey($orderId)->lockForUpdate()->first();
            if (! $order || $order->status !== 'pending') {
                return;
            }

            DB::table('users')->where('id', $order->user_id)->increment('balance', $order->total_price);
            if ($order->coupon_id) {
                CouponModel::whereKey($order->coupon_id)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }
            $order->idempotency_key = null;
            $order->status = 'cancelled';
            $order->save();
        });
    }

    public function getOrderDetails(string $orderId, string $requestingUserId, string $role): ?array
    {
        $order = $this->orderRepository->findById($orderId);
        if (! $order) {
            return null;
        }

        if ($role !== 'admin' && $order->getUserId() !== $requestingUserId) {
            throw new OrderException('Forbidden. Access denied.');
        }

        return [
            'order' => $order,
            'inventory_items' => [],
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
