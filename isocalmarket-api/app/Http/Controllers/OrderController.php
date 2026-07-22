<?php

namespace App\Http\Controllers;

use App\Domain\Exceptions\OrderException;
use App\Http\Requests\CreateOrderRequest;
use App\Application\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(CreateOrderRequest $request)
    {
        try {
            $userId = Auth::id();
            $itemsInput = $request->items;
            $couponCode = $request->input('coupon_code');

            $order = $this->orderService->createOrder($userId, $itemsInput, $couponCode);
            $freshUser = Auth::user()->fresh();

            return response()->json([
                'message' => 'Order created successfully.',
                'user' => [
                    'id' => (string)$freshUser->id,
                    'uid' => (string)$freshUser->uid,
                    'username' => $freshUser->username,
                    'name' => $freshUser->name,
                    'email' => $freshUser->email,
                    'phone' => $freshUser->phone,
                    'role' => $freshUser->role,
                    'google2fa_enabled' => $freshUser->google2fa_enabled,
                    'balance' => (float)$freshUser->balance,
                    'api_key' => $freshUser->api_key
                ],
                'order' => [
                    'id' => $order->getId(),
                    'order_code' => $order->getOrderCode(),
                    'total_price' => $order->getTotalPrice(),
                    'discount_amount' => $order->getDiscountAmount(),
                    'status' => $order->getStatus(),
                    'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s')
                ]
            ], 201);
        } catch (OrderException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to create order.');
        }
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $page = $request->query('page');
            $limit = $request->query('limit', 10);
            $status = $request->query('status');
            $search = $request->query('search');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $isAdmin = $user->role === 'admin';

            if ($page) {
                if ($isAdmin) {
                    $result = $this->orderService->getAllOrdersPaginated((int)$page, (int)$limit, $status, $search, $startDate, $endDate);
                } else {
                    $result = $this->orderService->getOrdersByUserPaginated($user->id, (int)$page, (int)$limit, $status, $search, $startDate, $endDate);
                }
                
                $orders = $result['items'];
                $total = $result['total'];

                $data = array_map(function ($order) use ($isAdmin) {
                    return [
                        'id' => $order->getId(),
                        'order_code' => $order->getOrderCode(),
                        'customer_email' => $order->getUserEmail(),
                        'customer_name' => $order->getUserName(),
                        'customer_uid' => $order->getUserUid(),
                        'total_price' => $order->getTotalPrice(),
                        'discount_amount' => $order->getDiscountAmount(),
                        'coupon_code' => $isAdmin ? $order->getCouponCode() : null,
                        'status' => $order->getStatus(),
                        'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
                        'items_count' => count($order->getItems())
                    ];
                }, $orders);

                return response()->json([
                    'data' => $data,
                    'meta' => [
                        'total' => $total,
                        'page' => (int)$page,
                        'limit' => (int)$limit,
                        'last_page' => (int)ceil($total / $limit)
                    ]
                ]);
            }

            if ($isAdmin) {
                $orders = $this->orderService->getAllOrders();
            } else {
                $orders = $this->orderService->getOrdersByUser($user->id);
            }

            $data = array_map(function ($order) use ($isAdmin) {
                return [
                    'id' => $order->getId(),
                    'order_code' => $order->getOrderCode(),
                    'customer_email' => $order->getUserEmail(),
                    'customer_name' => $order->getUserName(),
                    'customer_uid' => $order->getUserUid(),
                    'total_price' => $order->getTotalPrice(),
                    'discount_amount' => $order->getDiscountAmount(),
                    'coupon_code' => $isAdmin ? $order->getCouponCode() : null,
                    'status' => $order->getStatus(),
                    'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'items_count' => count($order->getItems())
                ];
            }, $orders);

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch orders.');
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            $details = $this->orderService->getOrderDetails(
                $id,
                (string)$user->id,
                $user->role
            );

            if (!$details) {
                return response()->json(['message' => 'Order not found.'], 404);
            }

            $order = $details['order'];
            $isAdmin = $user->role === 'admin';

            $itemsData = array_map(function ($item) {
                return [
                    'id' => $item->getId(),
                    'product_id' => $item->getProductId(),
                    'product_name' => $item->getProductName(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQuantity()
                ];
            }, $order->getItems());

            $accountsData = [];
            foreach ($order->getItems() as $item) {
                $keys = $item->getDeliveredKeys() ?: [];
                foreach ($keys as $key) {
                    $accountsData[] = [
                        'id' => null,
                        'product_id' => $item->getProductId(),
                        'data' => $key
                    ];
                }
            }

            return response()->json([
                'id' => $order->getId(),
                'order_code' => $order->getOrderCode(),
                'total_price' => $order->getTotalPrice(),
                'discount_amount' => $order->getDiscountAmount(),
                'coupon_code' => $isAdmin ? $order->getCouponCode() : null,
                'status' => $order->getStatus(),
                'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
                'items' => $itemsData,
                'accounts' => $accountsData
            ]);
        } catch (OrderException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch order details.');
        }
    }
}
