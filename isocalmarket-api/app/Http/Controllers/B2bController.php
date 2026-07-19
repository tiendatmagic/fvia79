<?php

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use App\Application\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class B2bController extends Controller
{
    private ProductService $productService;
    private OrderService $orderService;

    public function __construct(ProductService $productService, OrderService $orderService)
    {
        $this->productService = $productService;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        try {
            $page = (int)$request->query('page', 1);
            $limit = (int)$request->query('limit', 10);
            $categoryId = $request->query('category_id');
            $search = $request->query('search');

            $result = $this->productService->getActiveProductsPaginated($page, $limit, $categoryId, 'active', $search, 'active');
            $products = $result['items'];
            $total = $result['total'];

            $data = array_map(function ($product) {
                $category = $product->getCategoryId() ? \App\Infrastructure\Persistence\Eloquent\CategoryModel::find($product->getCategoryId()) : null;
                return [
                    'id' => $product->getId(),
                    'title' => $product->getName(),
                    'category' => $category ? $category->name : 'Uncategorized',
                    'price' => (float)$product->getPrice(),
                    'stock' => $product->getAvailableCount()
                ];
            }, $products);

            return response()->json([
                'success' => true,
                'products' => $data,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'last_page' => (int)ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch products: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/b2b/products/{id}
     * Xem thông tin chi tiết một sản phẩm
     */
    public function show($id)
    {
        try {
            $product = $this->productService->getProductById($id);
            if (!$product || $product->isDeleted() || $product->getStatus() !== 'active') {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $category = $product->getCategoryId() ? \App\Infrastructure\Persistence\Eloquent\CategoryModel::find($product->getCategoryId()) : null;
            return response()->json([
                'id' => $product->getId(),
                'title' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => (float)$product->getPrice(),
                'stock' => $product->getAvailableCount(),
                'category' => $category ? $category->name : 'Uncategorized'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/b2b/buy
     * Mua tài khoản sản phẩm
     */
    public function buy(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $quantity = (int)$request->input('quantity', 1);

            if (!$productId) {
                return response()->json(['message' => 'The product_id field is required.'], 400);
            }

            if ($quantity <= 0) {
                return response()->json(['message' => 'Quantity must be greater than 0.'], 400);
            }

            $userId = Auth::id();

            $itemsInput = [
                [
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]
            ];

            $order = $this->orderService->createOrder($userId, $itemsInput);

            $accounts = [];
            foreach ($order->getItems() as $item) {
                $keys = $item->getDeliveredKeys();
                if ($keys) {
                    $accounts = array_merge($accounts, $keys);
                }
            }

            return response()->json([
                'success' => true,
                'order_code' => $order->getOrderCode(),
                'total_price' => (float)$order->getTotalPrice(),
                'accounts' => $accounts
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/v1/b2b/me
     * Lấy thông tin tài khoản B2B hiện tại (bao gồm số dư)
     * Đảm bảo KHÔNG lộ password hay api_key
     */
    public function me(Request $request)
    {
        try {
            $userModel = Auth::user();
            if (!$userModel) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            return response()->json([
                'id' => $userModel->id,
                'name' => $userModel->name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'role' => $userModel->role,
                'balance' => (float)$userModel->balance
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch profile: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/b2b/orders/{order_code}
     * Tra cứu chi tiết đơn hàng theo order_code hoặc UUID
     */
    public function showOrder(Request $request, $orderCode)
    {
        try {
            $userId = Auth::id();

            $orderModel = \App\Infrastructure\Persistence\Eloquent\OrderModel::where('user_id', $userId)
                ->where(function ($q) use ($orderCode) {
                    $q->where('order_code', $orderCode)
                        ->orWhere('id', $orderCode);
                })
                ->first();

            if (!$orderModel && str_starts_with($orderCode, 'API-')) {
                $hex = strtolower(substr($orderCode, 4));
                $orderModel = \App\Infrastructure\Persistence\Eloquent\OrderModel::where('user_id', $userId)
                    ->where('id', 'like', $hex . '-%')
                    ->first();
            }

            if (!$orderModel) {
                return response()->json(['message' => 'Order not found.'], 404);
            }

            $accounts = [];
            foreach ($orderModel->items as $item) {
                if (is_array($item->delivered_keys)) {
                    $accounts = array_merge($accounts, $item->delivered_keys);
                }
            }

            return response()->json([
                'success' => true,
                'order_code' => $orderModel->order_code,
                'total_price' => (float)$orderModel->total_price,
                'status' => $orderModel->status,
                'created_at' => $orderModel->created_at->toIso8601String(),
                'accounts' => $accounts
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch order: ' . $e->getMessage()], 500);
        }
    }
}
