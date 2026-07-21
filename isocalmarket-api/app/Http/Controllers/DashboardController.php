<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $lowStockCategoryId = $request->query('low_stock_category_id');
            $lowStockSearch = $request->query('low_stock_search');
            $thresholdParam = $request->query('low_stock_threshold', '5');

            $lowStockPage = (int) $request->query('low_stock_page', 1);
            $lowStockLimit = (int) $request->query('low_stock_limit', 10);

            $cacheKey = 'admin_dashboard_stats_' . md5($startDate . '_' . $endDate . '_' . $lowStockCategoryId . '_' . $lowStockSearch . '_' . $thresholdParam . '_' . $lowStockPage . '_' . $lowStockLimit);

            $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $lowStockCategoryId, $lowStockSearch, $thresholdParam, $lowStockPage, $lowStockLimit) {
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

                // Tổng số tài nguyên khả dụng trong kho
                $totalAvailableInventory = (int) InventoryBatchModel::where('status', 'active')
                    ->selectRaw('SUM(initial_count - sold_count) as total')
                    ->value('total') ?? 0;

                // Xác định chế định lọc: lt (less than) hoặc gte (greater than or equal)
                if ($thresholdParam === 'gte_100') {
                    $compareMode = 'gte';
                    $threshold = 100;
                    $thresholdLabel = '≥ 100 items';
                } else {
                    $thresholdInput = (int) $thresholdParam;
                    $threshold = in_array($thresholdInput, [5, 10, 50, 100]) ? $thresholdInput : 5;
                    $compareMode = 'lt';
                    $thresholdLabel = '< ' . $threshold . ' items';
                }

                $productQuery = ProductModel::where('products.status', 'active')
                    ->select('products.id', 'products.name', 'products.slug', 'products.price', 'products.created_at')
                    ->selectSub(function ($query) {
                        $query->selectRaw('COALESCE(SUM(initial_count - sold_count), 0)')
                            ->from('inventory_batches')
                            ->whereColumn('product_id', 'products.id')
                            ->where('status', 'active');
                    }, 'available_count');

                if ($lowStockCategoryId) {
                    $productQuery->where('products.category_id', $lowStockCategoryId);
                }
                if ($lowStockSearch) {
                    $productQuery->where('products.name', 'like', "%{$lowStockSearch}%");
                }

                if ($compareMode === 'gte') {
                    $productQuery->having('available_count', '>=', $threshold);
                } else {
                    $productQuery->having('available_count', '<', $threshold);
                }

                $productQuery->orderBy('products.created_at', 'desc');

                $paginator = $productQuery->paginate($lowStockLimit, ['*'], 'low_stock_page', $lowStockPage);

                $lowStockProducts = [];
                foreach ($paginator->items() as $item) {
                    $lowStockProducts[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'price' => (float) $item->price,
                        'available_count' => (int) $item->available_count
                    ];
                }

                return [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_available_inventory' => $totalAvailableInventory,
                    'total_users' => $totalUsers,
                    'low_stock_products' => [
                        'data' => $lowStockProducts,
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total()
                    ],
                    'low_stock_threshold' => $thresholdParam,
                    'low_stock_threshold_label' => $thresholdLabel
                ];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
