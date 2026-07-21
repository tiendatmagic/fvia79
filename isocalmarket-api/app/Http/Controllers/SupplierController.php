<?php

namespace App\Http\Controllers;

use App\Application\Services\SupplierCatalogSyncService;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json(SupplierModel::orderBy('name')->get()->map(
            fn (SupplierModel $supplier) => $this->serialize($supplier),
        ));
    }

    public function update(Request $request, string $code)
    {
        $supplier = SupplierModel::where('code', $code)->firstOrFail();
        $validated = $request->validate([
            'api_key' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'enabled' => ['sometimes', 'boolean'],
            'auto_sync' => ['sometimes', 'boolean'],
            'sync_interval_minutes' => ['sometimes', 'integer', Rule::in([10, 30])],
            'price_adjustment_type' => ['sometimes', Rule::in(['fixed', 'percent'])],
            'price_adjustment_value' => ['sometimes', 'numeric', 'min:-1000000000', 'max:1000000000'],
        ]);

        if (($validated['api_key'] ?? null) === '') {
            unset($validated['api_key']);
        }
        $adjustmentType = $validated['price_adjustment_type'] ?? $supplier->price_adjustment_type;
        $adjustmentValue = $validated['price_adjustment_value'] ?? $supplier->price_adjustment_value;
        if ($adjustmentType === 'percent' && $adjustmentValue < -100) {
            return response()->json(['message' => 'Percentage adjustment cannot be below -100%.'], 422);
        }

        $supplier->update($validated);
        return response()->json($this->serialize($supplier->fresh()));
    }

    public function sync(string $code, SupplierCatalogSyncService $syncService)
    {
        $result = $syncService->syncByCode($code);
        $supplier = SupplierModel::where('code', $code)->firstOrFail();

        return response()->json([
            'message' => "{$supplier->name} catalog synchronized successfully.",
            'result' => $result,
            'supplier' => $this->serialize($supplier),
        ]);
    }

    private function serialize(SupplierModel $supplier): array
    {
        return [
            'code' => $supplier->code,
            'name' => $supplier->name,
            'base_url' => $supplier->base_url,
            'has_api_key' => (bool) ($supplier->api_key ?: config("services.{$supplier->code}.api_key")),
            'enabled' => $supplier->enabled,
            'auto_sync' => $supplier->auto_sync,
            'sync_interval_minutes' => $supplier->sync_interval_minutes,
            'price_adjustment_type' => $supplier->price_adjustment_type,
            'price_adjustment_value' => $supplier->price_adjustment_value,
            'last_synced_at' => $supplier->last_synced_at?->toIso8601String(),
            'last_sync_status' => $supplier->last_sync_status,
            'last_sync_error' => $supplier->last_sync_error,
            'products_count' => $supplier->products()->count(),
        ];
    }
}
