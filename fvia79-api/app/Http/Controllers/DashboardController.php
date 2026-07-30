<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
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

            $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 10, function () use ($startDate, $endDate, $lowStockCategoryId, $lowStockSearch, $thresholdParam, $lowStockPage, $lowStockLimit) {
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

                $todayStart = \Carbon\Carbon::now()->startOfDay()->toDateTimeString();
                $todayEnd = \Carbon\Carbon::now()->endOfDay()->toDateTimeString();
                $totalDepositToday = (float) DepositModel::where('status', 'completed')
                    ->where('created_at', '>=', $todayStart)
                    ->where('created_at', '<=', $todayEnd)
                    ->sum('amount_usdt');

                $monthStart = \Carbon\Carbon::now()->startOfMonth()->toDateTimeString();
                $monthEnd = \Carbon\Carbon::now()->endOfMonth()->toDateTimeString();
                $totalDepositMonth = (float) DepositModel::where('status', 'completed')
                    ->where('created_at', '>=', $monthStart)
                    ->where('created_at', '<=', $monthEnd)
                    ->sum('amount_usdt');

                $depositQuery = DepositModel::where('status', 'completed');
                if ($startDate) {
                    $start = strlen($startDate) === 10 ? $startDate . ' 00:00:00' : $startDate;
                    $depositQuery->where('created_at', '>=', $start);
                }
                if ($endDate) {
                    $end = strlen($endDate) === 10 ? $endDate . ' 23:59:59' : $endDate;
                    $depositQuery->where('created_at', '<=', $end);
                }
                $totalDepositFiltered = (float) $depositQuery->sum('amount_usdt');

                $cogsQuery = \App\Infrastructure\Persistence\Eloquent\OrderItemModel::query()
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', 'paid');

                $chartQuery = \App\Infrastructure\Persistence\Eloquent\OrderItemModel::query()
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', 'paid')
                    ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.price) as revenue, SUM(order_items.quantity * order_items.cost_price) as cogs')
                    ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(orders.created_at)'))
                    ->orderBy('date', 'desc')
                    ->take(60);

                if ($startDate) {
                    $start = strlen($startDate) === 10 ? $startDate . ' 00:00:00' : $startDate;
                    $cogsQuery->where('orders.created_at', '>=', $start);
                    $chartQuery->where('orders.created_at', '>=', $start);
                }
                if ($endDate) {
                    $end = strlen($endDate) === 10 ? $endDate . ' 23:59:59' : $endDate;
                    $cogsQuery->where('orders.created_at', '<=', $end);
                    $chartQuery->where('orders.created_at', '<=', $end);
                }

                $totalCogs = (float) $cogsQuery->sum(\Illuminate\Support\Facades\DB::raw('order_items.quantity * order_items.cost_price'));
                $totalProfit = $totalRevenue - $totalCogs;
                $profitMarginPercent = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0.0;

                $financialChart = $chartQuery->get()->map(function ($row) {
                    $rev = (float) $row->revenue;
                    $cogs = (float) $row->cogs;
                    return [
                        'date' => $row->date,
                        'revenue' => $rev,
                        'cogs' => $cogs,
                        'profit' => $rev - $cogs
                    ];
                })->toArray();

                $totalAvailableInventory = (int) InventoryBatchModel::where('status', '!=', 'inactive')
                    ->selectRaw('SUM(initial_count - sold_count) as total')
                    ->value('total') ?? 0;

                if (str_starts_with($thresholdParam, 'gte_')) {
                    $compareMode = 'gte';
                    $threshold = (int) substr($thresholdParam, 4);
                    if (!in_array($threshold, [10, 50, 100])) {
                        $threshold = 100;
                    }
                    $thresholdLabel = '≥ ' . $threshold . ' items';
                } else {
                    $thresholdInput = (int) $thresholdParam;
                    $threshold = in_array($thresholdInput, [5]) ? $thresholdInput : 5;
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

                $productQuery->groupBy('products.id', 'products.name', 'products.slug', 'products.price', 'products.created_at');

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
                    'total_cogs' => $totalCogs,
                    'total_profit' => $totalProfit,
                    'profit_margin_percent' => $profitMarginPercent,
                    'financial_chart' => $financialChart,
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_available_inventory' => $totalAvailableInventory,
                    'total_users' => $totalUsers,
                    'total_deposit_today' => $totalDepositToday,
                    'total_deposit_month' => $totalDepositMonth,
                    'total_deposit_filtered' => $totalDepositFiltered,
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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch admin dashboard stats.');
        }
    }
}
