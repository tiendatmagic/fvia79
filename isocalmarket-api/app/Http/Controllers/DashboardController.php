<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $totalRevenue = (float) OrderModel::where('status', 'paid')->sum('total_price');
            $totalOrders = OrderModel::count();
            $totalProducts = ProductModel::where('status', 'active')->count();

            // Tổng số tài nguyên khả dụng trong kho
            $batches = InventoryBatchModel::where('status', 'active')->get();
            $totalAvailableInventory = 0;
            foreach ($batches as $batch) {
                if (!empty($batch->keys_pool)) {
                    $lines = preg_split('/\r\n|\r|\n/', trim($batch->keys_pool));
                    $totalAvailableInventory += count(array_filter($lines, fn($l) => !empty(trim($l))));
                }
            }

            $products = ProductModel::where('status', 'active')->get();
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
                'low_stock_products' => $lowStockProducts
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
