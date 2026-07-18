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

    /**
     * GET /api/v1/b2b/products
     * Lấy danh sách sản phẩm hoạt động
     */
    public function index(Request $request)
    {
        try {
            $products = $this->productService->getAllActiveProducts(null, 'active', null, 'active');

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

            return response()->json($data);
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
                'transaction_id' => 'API-' . strtoupper(substr(str_replace('-', '', $order->getId()), 0, 8)),
                'total_price' => (float)$order->getTotalPrice(),
                'accounts' => $accounts
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
