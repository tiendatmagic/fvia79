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
            $costPrice = $request->has('cost_price') && $request->cost_price !== null ? (float) $request->cost_price : null;
            $batch = $this->inventoryService->importBatch(
                $request->product_id,
                $request->batch_name,
                $request->raw_data,
                $costPrice
            );

            return response()->json([
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

            return $this->serverError($e, 'Failed to import inventory batch.');
        }
    }

    public function show($id)
    {
        try {
            $item = $this->inventoryService->getItemById($id);
            if (!$item) {
                return response()->json(['message' => 'Inventory batch not found.'], 404);
            }
            return response()->json([
                'id' => $item->getId(),
                'product_id' => $item->getProductId(),
                'batch_name' => $item->getBatchName(),
                'cost_price' => $item->getCostPrice(),
                'keys_pool' => $item->getKeysPool(),
                'initial_count' => $item->getInitialCount(),
                'sold_count' => $item->getSoldCount(),
                'available_count' => $item->getAvailableCount(),
                'status' => $item->getStatus(),
                'created_at' => $item->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updated_at' => $item->getUpdatedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch inventory batch.');
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
                        'cost_price' => $item->getCostPrice(),
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
                    'cost_price' => $item->getCostPrice(),
                    'keys_pool' => $item->getKeysPool(),
                    'initial_count' => $item->getInitialCount(),
                    'sold_count' => $item->getSoldCount(),
                    'available_count' => $item->getAvailableCount(),
                    'status' => $item->getStatus(),
                    'created_at' => $item->getCreatedAt()?->format('Y-m-d H:i:s')
                ];
            }, $items);

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch inventory batches.');
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
                    'cost_price' => $updated->getCostPrice(),
                    'keys_pool' => $updated->getKeysPool(),
                    'initial_count' => $updated->getInitialCount(),
                    'sold_count' => $updated->getSoldCount(),
                    'available_count' => $updated->getAvailableCount(),
                    'status' => $updated->getStatus()
                ]
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() === __('messages.inventory_conflict')) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return $this->serverError($e, 'Failed to update inventory batch.');
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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to delete inventory batch.');
        }
    }

    public function listResourceItems(Request $request)
    {
        try {
            $filters = [
                'product_id' => $request->query('product_id'),
                'batch_id' => $request->query('batch_id'),
                'status' => $request->query('status'),
                'search' => $request->query('search')
            ];

            $page = (int) $request->query('page', 1);
            $limit = (int) $request->query('limit', 10);

            $result = $this->inventoryService->getResourceItemsPaginated($filters, $page, $limit);
            $items = $result['items'];
            $total = $result['total'];

            $data = array_map(function ($item) {
                return [
                    'id' => $item->getId(),
                    'batch_id' => $item->getBatchId(),
                    'product_id' => $item->getProductId(),
                    'secret_data' => $item->getSecretData(),
                    'status' => $item->getStatus(),
                    'order_id' => $item->getOrderId(),
                    'sold_at' => $item->getSoldAt()?->format('Y-m-d H:i:s'),
                    'created_at' => $item->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'batch_name' => $item->getBatchName(),
                    'product_name' => $item->getProductName(),
                ];
            }, $items);

            return response()->json([
                'items' => $data,
                'total' => $total,
                'current_page' => $page,
                'last_page' => ceil($total / $limit)
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch resource items.');
        }
    }

    public function updateResourceItem(Request $request, $id)
    {
        try {
            $updated = $this->inventoryService->updateResourceItem($id, $request->all());
            if (!$updated) {
                return response()->json(['message' => 'Resource item not found.'], 404);
            }

            return response()->json([
                'message' => 'Resource item updated successfully.',
                'data' => [
                    'id' => $updated->getId(),
                    'batch_id' => $updated->getBatchId(),
                    'product_id' => $updated->getProductId(),
                    'secret_data' => $updated->getSecretData(),
                    'status' => $updated->getStatus(),
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to update resource item.');
        }
    }

    public function destroyResourceItem($id)
    {
        try {
            $deleted = $this->inventoryService->deleteResourceItem($id);
            if (!$deleted) {
                return response()->json(['message' => 'Resource item not found.'], 404);
            }

            return response()->json(['message' => 'Resource item deleted successfully.']);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to delete resource item.');
        }
    }
}
