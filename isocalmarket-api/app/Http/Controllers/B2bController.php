<?php

namespace App\Http\Controllers;

use App\Application\Services\OrderService;
use App\Application\Services\ProductService;
use App\Domain\Exceptions\OrderException;
use App\Http\Requests\B2bBuyRequest;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
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
            $page = max(1, (int) $request->query('page', 1));
            $limit = min(100, max(1, (int) $request->query('limit', 10)));
            $categoryId = $request->query('category_id');
            $search = $request->query('search');
            $lang = $this->resolveLanguage($request);

            $result = $this->productService->getActiveProductsPaginated($page, $limit, $categoryId, 'active', $search, 'active');
            $products = $result['items'];
            $total = $result['total'];

            $data = array_map(function ($product) use ($lang) {
                $category = $product->getCategoryId() ? CategoryModel::find($product->getCategoryId()) : null;

                return [
                    'id' => $product->getId(),
                    'title' => $product->getLocalizedName($lang),
                    'name_en' => $product->getNameEn(),
                    'name_vi' => $product->getNameVi(),
                    'category' => $category ? $category->getLocalizedName($lang) : 'Uncategorized',
                    'category_en' => $category?->name_en,
                    'category_vi' => $category?->name_vi,
                    'price' => (float) $product->getPrice(),
                    'stock' => $product->getAvailableCount(),
                ];
            }, $products);

            return response()->json([
                'success' => true,
                'products' => $data,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'last_page' => (int) ceil($total / $limit),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch B2B products.');
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $product = $this->productService->getProductById($id);
            if (! $product || $product->isDeleted() || $product->getStatus() !== 'active') {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $category = $product->getCategoryId() ? CategoryModel::find($product->getCategoryId()) : null;
            if ($category && ! $category->is_active) {
                return response()->json(['message' => 'Product not found.'], 404);
            }
            $lang = $this->resolveLanguage($request);

            return response()->json([
                'id' => $product->getId(),
                'title' => $product->getLocalizedName($lang),
                'name_en' => $product->getNameEn(),
                'name_vi' => $product->getNameVi(),
                'description' => $product->getLocalizedDescription($lang),
                'description_en' => $product->getDescriptionEn(),
                'description_vi' => $product->getDescriptionVi(),
                'price' => (float) $product->getPrice(),
                'stock' => $product->getAvailableCount(),
                'category' => $category ? $category->getLocalizedName($lang) : 'Uncategorized',
                'category_en' => $category?->name_en,
                'category_vi' => $category?->name_vi,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch B2B product.');
        }
    }

    public function buy(B2bBuyRequest $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validated();
            $couponCode = $validated['coupon_code'] ?? null;
            $idempotencyKey = $validated['idempotency_key'];

            $itemsInput = [];

            if (isset($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $itemsInput[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'] ?? 1,
                    ];
                }
            } else {
                $itemsInput[] = [
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'] ?? 1,
                ];
            }

            $order = $this->orderService->createOrder(
                $userId,
                $itemsInput,
                $couponCode,
                $idempotencyKey,
            );

            $accounts = [];
            foreach ($order->getItems() as $item) {
                $keys = $item->getDeliveredKeys();
                if ($keys) {
                    $accounts = array_merge($accounts, $keys);
                }
            }

            $isPending = $order->isPending();

            return response()->json([
                'success' => ! $isPending,
                'status' => $order->getStatus(),
                'message' => $isPending
                    ? 'Order is pending supplier reconciliation.'
                    : 'Order created successfully.',
                'order_code' => $order->getOrderCode(),
                'total_price' => (float) $order->getTotalPrice(),
                'discount_amount' => (float) $order->getDiscountAmount(),
                'accounts' => $accounts,
            ], $isPending ? 202 : 200);
        } catch (OrderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to create B2B order.');
        }
    }

    public function me(Request $request)
    {
        try {
            $userModel = Auth::user();
            if (! $userModel) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            return response()->json([
                'id' => $userModel->id,
                'uid' => $userModel->uid,
                'username' => $userModel->username,
                'name' => $userModel->name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'role' => $userModel->role,
                'balance' => (float) $userModel->balance,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch B2B profile.');
        }
    }

    public function showOrder(Request $request, $orderCode)
    {
        try {
            $userId = Auth::id();

            $orderModel = OrderModel::where('user_id', $userId)
                ->where(function ($q) use ($orderCode) {
                    $q->where('order_code', $orderCode)
                        ->orWhere('id', $orderCode);
                })
                ->first();

            if (! $orderModel && str_starts_with($orderCode, 'API-')) {
                $hex = strtolower(substr($orderCode, 4));
                $orderModel = OrderModel::where('user_id', $userId)
                    ->where('id', 'like', $hex.'-%')
                    ->first();
            }

            if (! $orderModel) {
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
                'total_price' => (float) $orderModel->total_price,
                'discount_amount' => (float) $orderModel->discount_amount,
                'status' => $orderModel->status,
                'created_at' => $orderModel->created_at->toIso8601String(),
                'accounts' => $accounts,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch B2B order.');
        }
    }

    private function resolveLanguage(Request $request): string
    {
        $lang = strtolower(trim((string) ($request->query('lang') ?? $request->header('Accept-Language') ?? 'en')));
        $lang = preg_split('/[-_,;]/', $lang, 2)[0] ?? 'en';

        return in_array($lang, ['en', 'vi'], true) ? $lang : 'en';
    }
}
