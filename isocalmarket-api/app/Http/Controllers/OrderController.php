<?php

namespace App\Http\Controllers;

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
            
            // Format items input: items.*.product_id, items.*.quantity
            $itemsInput = $request->items;

            $order = $this->orderService->createOrder($userId, $itemsInput);

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => [
                    'id' => $order->getId(),
                    'order_code' => $order->getOrderCode(),
                    'total_price' => $order->getTotalPrice(),
                    'status' => $order->getStatus(),
                    'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s')
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $page = $request->query('page');
            $limit = $request->query('limit', 10); // Mặc định 10 đơn hàng/trang
            $status = $request->query('status'); // Lọc theo trạng thái đơn hàng

            if ($page) {
                if ($user->role === 'admin') {
                    $result = $this->orderService->getAllOrdersPaginated((int)$page, (int)$limit, $status);
                } else {
                    $result = $this->orderService->getOrdersByUserPaginated($user->id, (int)$page, (int)$limit, $status);
                }
                
                $orders = $result['items'];
                $total = $result['total'];

                $data = array_map(function ($order) {
                    return [
                        'id' => $order->getId(),
                        'order_code' => $order->getOrderCode(),
                        'customer_email' => $order->getUserEmail(),
                        'total_price' => $order->getTotalPrice(),
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

            if ($user->role === 'admin') {
                $orders = $this->orderService->getAllOrders();
            } else {
                $orders = $this->orderService->getOrdersByUser($user->id);
            }

            $data = array_map(function ($order) {
                return [
                    'id' => $order->getId(),
                    'order_code' => $order->getOrderCode(),
                    'customer_email' => $order->getUserEmail(),
                    'total_price' => $order->getTotalPrice(),
                    'status' => $order->getStatus(),
                    'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'items_count' => count($order->getItems())
                ];
            }, $orders);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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
            $inventoryItems = $details['inventory_items'];

            $itemsData = array_map(function ($item) {
                return [
                    'id' => $item->getId(),
                    'product_id' => $item->getProductId(),
                    'product_name' => $item->getProductName(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQuantity()
                ];
            }, $order->getItems());

            $accountsData = array_map(function ($acc) {
                return [
                    'id' => $acc->getId(),
                    'product_id' => $acc->getProductId(),
                    'data' => $acc->getData() // Raw data tài khoản đã mua
                ];
            }, $inventoryItems);

            return response()->json([
                'id' => $order->getId(),
                'order_code' => $order->getOrderCode(),
                'total_price' => $order->getTotalPrice(),
                'status' => $order->getStatus(),
                'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
                'items' => $itemsData,
                'accounts' => $accountsData
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
