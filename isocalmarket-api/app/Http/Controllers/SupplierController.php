<?php

namespace App\Http\Controllers;

use App\Application\Services\SupplierCatalogSyncService;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\CloneStoreConnector;
use App\Integrations\Suppliers\SupplierUrlGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class SupplierController extends Controller
{
    public function __construct(
        private readonly CloneStoreConnector $connector,
        private readonly SupplierUrlGuard $urlGuard,
    ) {}

    public function index()
    {
        return response()->json(SupplierModel::orderBy('name')->get()->map(
            fn (SupplierModel $supplier) => $this->serialize($supplier),
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'base_url' => ['required', 'url:https', 'max:255'],
            'api_key' => ['required', 'string', 'max:2000'],
            'enabled' => ['sometimes', 'boolean'],
            'auto_sync' => ['sometimes', 'boolean'],
            'sync_interval_minutes' => ['sometimes', 'integer', Rule::in([10, 30])],
            'price_adjustment_type' => ['sometimes', Rule::in(['fixed', 'percent'])],
            'price_adjustment_value' => ['sometimes', 'numeric', 'min:-1000000000', 'max:1000000000'],
        ]);

        try {
            $validated['base_url'] = $this->urlGuard->normalizeAndValidate($validated['base_url']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $supplier = new SupplierModel($validated);
        $supplier->code = $this->uniqueCode($validated['name'], $validated['base_url']);
        try {
            $supplier->catalog_driver = $this->connector->detectDriver($supplier);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
        $supplier->save();

        return response()->json($this->serialize($supplier), 201);
    }

    public function update(Request $request, string $code)
    {
        $supplier = SupplierModel::where('code', $code)->firstOrFail();
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'base_url' => ['sometimes', 'required', 'url:https', 'max:255'],
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
        if (isset($validated['base_url'])) {
            try {
                $validated['base_url'] = $this->urlGuard->normalizeAndValidate($validated['base_url']);
            } catch (InvalidArgumentException $exception) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }
        }
        $adjustmentType = $validated['price_adjustment_type'] ?? $supplier->price_adjustment_type;
        $adjustmentValue = $validated['price_adjustment_value'] ?? $supplier->price_adjustment_value;
        if ($adjustmentType === 'percent' && $adjustmentValue < -100) {
            return response()->json(['message' => 'Percentage adjustment cannot be below -100%.'], 422);
        }

        $mustDetectDriver = array_key_exists('base_url', $validated) || array_key_exists('api_key', $validated);
        $supplier->fill($validated);
        if ($mustDetectDriver) {
            try {
                $supplier->catalog_driver = $this->connector->detectDriver($supplier);
            } catch (RuntimeException $exception) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }
        }
        $supplier->save();

        return response()->json($this->serialize($supplier->fresh()));
    }

    public function sync(string $code, SupplierCatalogSyncService $syncService)
    {
        try {
            $result = $syncService->syncByCode($code);
            $supplier = SupplierModel::where('code', $code)->firstOrFail();

            return response()->json([
                'message' => "{$supplier->name} catalog synchronized successfully.",
                'result' => $result,
                'supplier' => $this->serialize($supplier),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Supplier not found.'], 404);
        } catch (ConnectionException|RequestException $exception) {
            return $this->upstreamError(
                $exception,
                "Supplier catalog request failed for [{$code}].",
                'Supplier service is temporarily unavailable.',
            );
        } catch (RuntimeException $exception) {
            if (str_ends_with($exception->getMessage(), ' is disabled.')) {
                return response()->json(['message' => 'Supplier is disabled.'], 422);
            }

            return $this->upstreamError(
                $exception,
                "Supplier catalog synchronization failed for [{$code}].",
                'Supplier synchronization failed.',
            );
        } catch (\Throwable $exception) {
            return $this->serverError($exception, "Unexpected supplier sync failure for [{$code}].");
        }
    }

    private function serialize(SupplierModel $supplier): array
    {
        return [
            'code' => $supplier->code,
            'name' => $supplier->name,
            'base_url' => $supplier->base_url,
            'catalog_driver' => $supplier->catalog_driver,
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

    private function uniqueCode(string $name, string $baseUrl): string
    {
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $base = Str::slug($host ?: $name) ?: 'supplier';
        $code = Str::limit($base, 45, '');
        $suffix = 2;

        while (SupplierModel::where('code', $code)->exists()) {
            $code = Str::limit($base, 40, '').'-'.$suffix++;
        }

        return $code;
    }
}
