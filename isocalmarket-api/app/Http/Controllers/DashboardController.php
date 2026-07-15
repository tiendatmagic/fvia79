<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        try {
            // Tổng doanh thu của các đơn đã thanh toán (paid)
            $totalRevenue = (float) OrderModel::where('status', 'paid')->sum('total_price');

            // Tổng số đơn hàng
            $totalOrders = OrderModel::count();

            // Tổng số sản phẩm hoạt động
            $totalProducts = ProductModel::where('status', 'active')->count();

            // Tổng số tài nguyên khả dụng trong kho
            $totalAvailableInventory = InventoryItemModel::where('status', 'available')->count();

            // Lấy danh sách sản phẩm và lọc ra các sản phẩm sắp hết hàng (tồn kho khả dụng < 5)
            $products = ProductModel::where('status', 'active')->get();
            $lowStockProducts = [];

            foreach ($products as $product) {
                $availableCount = InventoryItemModel::where('product_id', $product->id)
                    ->where('status', 'available')
                    ->count();

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
