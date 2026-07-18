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

            $cacheKey = 'admin_dashboard_stats_' . md5($startDate . '_' . $endDate . '_' . $lowStockCategoryId . '_' . $lowStockSearch . '_' . $thresholdParam);

            $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $lowStockCategoryId, $lowStockSearch, $thresholdParam) {
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

                $productQuery = ProductModel::where('status', 'active');
                if ($lowStockCategoryId) {
                    $productQuery->where('category_id', $lowStockCategoryId);
                }
                if ($lowStockSearch) {
                    $productQuery->where('name', 'like', "%{$lowStockSearch}%");
                }

                $products = $productQuery->get();
                $lowStockProducts = [];

                foreach ($products as $product) {
                    $availableCount = (int) InventoryBatchModel::where('product_id', $product->id)
                        ->where('status', 'active')
                        ->selectRaw('SUM(initial_count - sold_count) as total')
                        ->value('total') ?? 0;

                    $matches = $compareMode === 'gte'
                        ? $availableCount >= $threshold
                        : $availableCount < $threshold;

                    if ($matches) {
                        $lowStockProducts[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'slug' => $product->slug,
                            'price' => (float) $product->price,
                            'available_count' => $availableCount
                        ];
                    }
                }

                return [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_available_inventory' => $totalAvailableInventory,
                    'total_users' => $totalUsers,
                    'low_stock_products' => $lowStockProducts,
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
