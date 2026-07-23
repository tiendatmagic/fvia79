<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use Illuminate\Support\Facades\Auth;

class B2bAdminController extends Controller
{
    public function indexUsers(Request $request)
    {
        try {
            $page = (int)$request->query('page', 1);
            $limit = (int)$request->query('limit', 10);
            $search = $request->query('search');
            $role = $request->query('role');
            $hasApiKey = $request->query('has_api_key');

            $query = UserModel::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', $search)
                      ->orWhere('uid', $search)
                      ->orWhere('username', $search)
                      ->orWhere('email', $search)
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('uid', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }

            if ($role) {
                $query->where('role', $role);
            }

            if ($hasApiKey !== null && $hasApiKey !== '') {
                if ($hasApiKey === 'yes' || $hasApiKey === '1' || $hasApiKey === 'true') {
                    $query->whereNotNull('api_key')->where('api_key', '!=', '');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('api_key')->orWhere('api_key', '');
                    });
                }
            }

            $total = $query->count();
            $users = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get()
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'uid' => $u->uid,
                        'username' => $u->username,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'role' => $u->role,
                        'balance' => (float)$u->balance,
                        'has_api_key' => !empty($u->api_key)
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'last_page' => (int)ceil($total / $limit)
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch B2B users.');
        }
    }

    public function indexOrders(Request $request)
    {
        try {
            $page = (int)$request->query('page', 1);
            $limit = (int)$request->query('limit', 10);
            $status = $request->query('status');
            $userId = $request->query('user_id');
            $search = $request->query('search');

            $query = OrderModel::with(['items', 'user']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhereHas('user', function ($uq) use ($userId) {
                          $uq->where('uid', $userId);
                      });
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('username', 'like', "%{$search}%")
                             ->orWhere('uid', 'like', "%{$search}%")
                             ->orWhere('id', 'like', "%{$search}%");
                      });
                });
            }

            $query->orderBy('created_at', 'desc');

            $total = $query->count();
            $orders = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get()
                ->map(function ($order) {
                    $accounts = [];
                    foreach ($order->items as $item) {
                        if (is_array($item->delivered_keys)) {
                            $accounts = array_merge($accounts, $item->delivered_keys);
                        }
                    }
                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'user' => [
                            'id' => $order->user->id,
                            'uid' => $order->user->uid,
                            'username' => $order->user->username,
                            'name' => $order->user->name,
                            'email' => $order->user->email,
                        ],
                        'total_price' => (float)$order->total_price,
                        'discount_amount' => (float)$order->discount_amount,
                        'coupon_code' => $order->coupon_code,
                        'status' => $order->status,
                        'created_at' => $order->created_at->toIso8601String(),
                        'accounts_count' => count($accounts)
                    ];
                });

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'last_page' => (int)ceil($total / $limit)
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch B2B orders.');
        }
    }

    public function importInventory(Request $request, $id, \App\Application\Services\InventoryService $inventoryService)
    {
        try {
            $productExists = \App\Infrastructure\Persistence\Eloquent\ProductModel::where('id', $id)->exists();
            if (!$productExists) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $request->validate([
                'batch_name' => 'nullable|string',
                'content' => 'nullable|string',
                'raw_data' => 'nullable|string',
                'cost_price' => 'nullable|numeric|min:0',
                'file' => 'nullable|file',
            ]);

            $batchName = $request->input('batch_name') ?: 'API Import - ' . now()->format('Y-m-d H:i:s');
            $costPrice = $request->input('cost_price') !== null ? (float)$request->input('cost_price') : null;
            
            $rawData = '';
            if ($request->hasFile('file')) {
                $rawData = $request->file('file')->get();
            } else {
                $rawData = $request->input('content') ?: $request->input('raw_data', '');
            }

            if (empty(trim($rawData))) {
                return response()->json(['message' => 'Missing inventory content or file.'], 422);
            }

            $batch = $inventoryService->importBatch(
                $id,
                $batchName,
                $rawData,
                $costPrice
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully imported batch '{$batch->getBatchName()}' with {$batch->getInitialCount()} resources.",
                'data' => [
                    'id' => $batch->getId(),
                    'product_id' => $batch->getProductId(),
                    'batch_name' => $batch->getBatchName(),
                    'cost_price' => $batch->getCostPrice(),
                    'initial_count' => $batch->getInitialCount(),
                    'sold_count' => $batch->getSoldCount(),
                    'available_count' => $batch->getAvailableCount(),
                    'status' => $batch->getStatus()
                ]
            ], 201);
        } catch (\Exception $e) {
            if (in_array($e->getMessage(), ['Product not found.', 'No resource keys found in raw data.'], true)) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return $this->serverError($e, 'Failed to import B2B inventory.');
        }
    }

    public function getStats(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $revenueQuery = OrderModel::where('status', 'paid');
            $ordersQuery = OrderModel::query();

            if ($startDate) {
                $start = strlen($startDate) === 10 ? $startDate . ' 00:00:00' : $startDate;
                $revenueQuery->where('created_at', '>=', $start);
                $ordersQuery->where('created_at', '>=', $start);
            }
            if ($endDate) {
                $end = strlen($endDate) === 10 ? $endDate . ' 23:59:59' : $endDate;
                $revenueQuery->where('created_at', '<=', $end);
                $ordersQuery->where('created_at', '<=', $end);
            }

            $totalRevenue = (float) $revenueQuery->sum('total_price');
            $totalOrders = $ordersQuery->count();
            $totalProducts = ProductModel::where('status', 'active')->count();
            $totalUsers = UserModel::count();

            $cogsQuery = \App\Infrastructure\Persistence\Eloquent\OrderItemModel::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'paid');
                if ($startDate) {
                    $start = strlen($startDate) === 10 ? $startDate . ' 00:00:00' : $startDate;
                    $q->where('created_at', '>=', $start);
                }
                if ($endDate) {
                    $end = strlen($endDate) === 10 ? $endDate . ' 23:59:59' : $endDate;
                    $q->where('created_at', '<=', $end);
                }
            });

            $totalCogs = (float) ($cogsQuery->selectRaw('SUM(cost_price * quantity) as cogs')->value('cogs') ?? 0.0);
            $totalProfit = $totalRevenue - $totalCogs;
            $profitMarginPercent = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0.0;

            $totalAvailableInventory = (int) InventoryBatchModel::where('status', 'active')
                ->selectRaw('SUM(initial_count - sold_count) as total')
                ->value('total') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'total_cogs' => $totalCogs,
                    'total_profit' => $totalProfit,
                    'profit_margin_percent' => $profitMarginPercent,
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_available_inventory' => $totalAvailableInventory,
                    'total_users' => $totalUsers
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch B2B dashboard stats.');
        }
    }
}
