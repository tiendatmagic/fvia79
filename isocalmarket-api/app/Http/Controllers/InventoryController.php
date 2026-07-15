<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportInventoryRequest;
use App\Application\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function import(ImportInventoryRequest $request)
    {
        try {
            $importedCount = $this->inventoryService->importItems(
                $request->product_id,
                $request->raw_data
            );

            return response()->json([
                'message' => "Successfully imported {$importedCount} inventory items."
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'product_id' => $request->query('product_id'),
                'status' => $request->query('status')
            ];

            $page = $request->query('page');
            $limit = $request->query('limit', 10); // Mặc định 10 item/trang trong admin quản lý kho

            if ($page) {
                $result = $this->inventoryService->getItemsPaginated($filters, (int)$page, (int)$limit);
                $items = $result['items'];
                $total = $result['total'];

                $data = array_map(function ($item) {
                    return [
                        'id' => $item->getId(),
                        'product_id' => $item->getProductId(),
                        'data' => $item->getData(), // Cho phép admin xem raw data
                        'status' => $item->getStatus(),
                        'order_id' => $item->getOrderId()
                    ];
                }, $items);

                return response()->json([
                    'data' => $data,
                    'meta' => [
                        'total' => $total,
                        'page' => (int)$page,
                        'limit' => (int)$limit,
                        'last_page' => (int)ceil($total / $limit)
                    ]
                ]);
            }

            $items = $this->inventoryService->getItems($filters);

            $data = array_map(function ($item) {
                return [
                    'id' => $item->getId(),
                    'product_id' => $item->getProductId(),
                    'data' => $item->getData(),
                    'status' => $item->getStatus(),
                    'order_id' => $item->getOrderId()
                ];
            }, $items);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $deleted = $this->inventoryService->deleteItem($id);
            if (!$deleted) {
                return response()->json(['message' => 'Inventory item not found.'], 404);
            }

            return response()->json(['message' => 'Inventory item deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
