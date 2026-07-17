<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
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
            $batch = $this->inventoryService->importBatch(
                $request->product_id,
                $request->batch_name,
                $request->raw_data
            );

            return response()->json([
                'message' => "Successfully imported batch '{$batch->getBatchName()}' with {$batch->getInitialCount()} resources.",
                'data' => [
                    'id' => $batch->getId(),
                    'product_id' => $batch->getProductId(),
                    'batch_name' => $batch->getBatchName(),
                    'initial_count' => $batch->getInitialCount(),
                    'sold_count' => $batch->getSoldCount(),
                    'available_count' => $batch->getAvailableCount(),
                    'status' => $batch->getStatus()
                ]
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
                'status' => $request->query('status'),
                'search' => $request->query('search')
            ];

            $page = $request->query('page');
            $limit = $request->query('limit', 10);

            if ($page) {
                $result = $this->inventoryService->getItemsPaginated($filters, (int)$page, (int)$limit);
                $items = $result['items'];
                $total = $result['total'];

                $data = array_map(function ($item) {
                    return [
                        'id' => $item->getId(),
                        'product_id' => $item->getProductId(),
                        'batch_name' => $item->getBatchName(),
                        'keys_pool' => $item->getKeysPool(),
                        'initial_count' => $item->getInitialCount(),
                        'sold_count' => $item->getSoldCount(),
                        'available_count' => $item->getAvailableCount(),
                        'status' => $item->getStatus(),
                        'created_at' => $item->getCreatedAt()?->format('Y-m-d H:i:s')
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
                    'batch_name' => $item->getBatchName(),
                    'keys_pool' => $item->getKeysPool(),
                    'initial_count' => $item->getInitialCount(),
                    'sold_count' => $item->getSoldCount(),
                    'available_count' => $item->getAvailableCount(),
                    'status' => $item->getStatus(),
                    'created_at' => $item->getCreatedAt()?->format('Y-m-d H:i:s')
                ];
            }, $items);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateInventoryRequest $request, $id)
    {
        try {
            $updated = $this->inventoryService->updateBatch($id, $request->validated());
            if (!$updated) {
                return response()->json(['message' => 'Inventory batch not found.'], 404);
            }

            return response()->json([
                'message' => 'Inventory batch updated successfully.',
                'data' => [
                    'id' => $updated->getId(),
                    'product_id' => $updated->getProductId(),
                    'batch_name' => $updated->getBatchName(),
                    'keys_pool' => $updated->getKeysPool(),
                    'initial_count' => $updated->getInitialCount(),
                    'sold_count' => $updated->getSoldCount(),
                    'available_count' => $updated->getAvailableCount(),
                    'status' => $updated->getStatus()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->inventoryService->deleteItem($id);
            if (!$deleted) {
                return response()->json(['message' => 'Inventory batch not found.'], 404);
            }

            return response()->json(['message' => 'Inventory batch deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
