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
            $batches = InventoryBatchModel::where('status', 'active')->get();
            $totalAvailableInventory = 0;
            foreach ($batches as $batch) {
                if (!empty($batch->keys_pool)) {
                    $lines = preg_split('/\r\n|\r|\n/', trim($batch->keys_pool));
                    $totalAvailableInventory += count(array_filter($lines, fn($l) => !empty(trim($l))));
                }
            }

            $lowStockCategoryId = $request->query('low_stock_category_id');
            $lowStockSearch = $request->query('low_stock_search');

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
                $productBatches = InventoryBatchModel::where('product_id', $product->id)
                    ->where('status', 'active')
                    ->get();
                $availableCount = 0;
                foreach ($productBatches as $batch) {
                    if (!empty($batch->keys_pool)) {
                        $lines = preg_split('/\r\n|\r|\n/', trim($batch->keys_pool));
                        $availableCount += count(array_filter($lines, fn($l) => !empty(trim($l))));
                    }
                }

                if ($availableCount < 5) {
                    $lowStockProducts[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => (float) $product->price,
                        'available_count' => $availableCount
                    ];
                }
            }

            return response()->json([
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'total_products' => $totalProducts,
                'total_available_inventory' => $totalAvailableInventory,
                'total_users' => $totalUsers,
                'low_stock_products' => $lowStockProducts
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
