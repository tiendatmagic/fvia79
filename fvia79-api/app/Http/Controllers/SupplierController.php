<?php

namespace App\Http\Controllers;

use App\Application\Services\SupplierCatalogSyncService;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\CloneStoreConnector;
use App\Integrations\Suppliers\Muabm365Connector;
use App\Integrations\Suppliers\SupplierConnectorFactory;
use App\Integrations\Suppliers\SupplierOperationInProgressException;
use App\Integrations\Suppliers\SupplierOperationLock;
use App\Integrations\Suppliers\SupplierUrlGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class SupplierController extends Controller
{
    public function __construct(
        private readonly CloneStoreConnector $connector,
        private readonly SupplierUrlGuard $urlGuard,
        private readonly SupplierOperationLock $operationLock,
        private readonly SupplierConnectorFactory $connectorFactory,
    ) {}

    public function index()
    {
        $query = SupplierModel::orderBy('name');
        if (! config('services.muabm365.enabled')) {
            $query->where('catalog_driver', '!=', 'muabm365')->where('code', '!=', 'muabm365');
        }

        return response()->json($query->get()->map(
            fn (SupplierModel $supplier) => $this->serialize($supplier),
        ));
    }

    public function templates()
    {
        $templates = [
            [
                'code' => 'ads7979',
                'name' => 'ADS7979',
                'base_url' => 'https://ads7979.com',
                'catalog_driver' => 'clone_store_v2',
                'enabled' => true,
            ],
        ];

        if (config('services.muabm365.enabled')) {
            $templates[] = [
                'code' => 'muabm365',
                'name' => 'MUABM365',
                'base_url' => 'https://muabm365.top',
                'catalog_driver' => 'muabm365',
                'enabled' => true,
            ];
        }

        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'base_url' => ['required', 'url:https', 'max:255'],
            'api_key' => ['required', 'string', 'max:2000'],
            'catalog_driver' => ['sometimes', 'nullable', 'string', Rule::in(['clone_store_v1', 'clone_store_v2', 'muabm365'])],
            'enabled' => ['sometimes', 'boolean'],
            'products_enabled' => ['sometimes', 'boolean'],
            'import_out_of_stock' => ['sometimes', 'boolean'],
            'auto_sync' => ['sometimes', 'boolean'],
            'auto_translate' => ['sometimes', 'boolean'],
            'target_language' => ['sometimes', 'nullable', 'string', 'max:10'],
            'sync_interval_minutes' => ['sometimes', 'integer', Rule::in([1, 2, 5, 10, 30])],
            'price_adjustment_type' => ['sometimes', Rule::in(['fixed', 'percent'])],
            'price_adjustment_value' => ['sometimes', 'numeric', 'min:-1000000000', 'max:1000000000'],
            'round_vnd_prices' => ['sometimes', 'boolean'],
        ]);

        try {
            $validated['base_url'] = $this->urlGuard->normalizeAndValidate($validated['base_url']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $supplier = new SupplierModel($validated);
        $supplier->code = $this->uniqueCode($validated['name'], $validated['base_url']);
        try {
            if (empty($supplier->catalog_driver)) {
                $supplier->catalog_driver = $this->connectorFactory->detectDriver($supplier);
            }
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
            'products_enabled' => ['sometimes', 'boolean'],
            'import_out_of_stock' => ['sometimes', 'boolean'],
            'auto_sync' => ['sometimes', 'boolean'],
            'auto_translate' => ['sometimes', 'boolean'],
            'target_language' => ['sometimes', 'nullable', 'string', 'max:10'],
            'sync_interval_minutes' => ['sometimes', 'integer', Rule::in([1, 2, 5, 10, 30])],
            'price_adjustment_type' => ['sometimes', Rule::in(['fixed', 'percent'])],
            'price_adjustment_value' => ['sometimes', 'numeric', 'min:-1000000000', 'max:1000000000'],
            'round_vnd_prices' => ['sometimes', 'boolean'],
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
        $productsEnabledChanged = array_key_exists('products_enabled', $validated)
            && (bool) $validated['products_enabled'] !== $supplier->products_enabled;
        $supplier->fill($validated);
        if ($mustDetectDriver) {
            try {
                $supplier->catalog_driver = $this->connectorFactory->detectDriver($supplier);
            } catch (RuntimeException $exception) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }
        }
        try {
            $this->operationLock->run($supplier, function () use ($supplier, $productsEnabledChanged): void {
                DB::transaction(function () use ($supplier, $productsEnabledChanged): void {
                    $supplier->save();
                    if (! $productsEnabledChanged) {
                        return;
                    }

                    if ($supplier->products_enabled) {
                        $supplier->products()
                            ->whereNotNull('supplier_previous_status')
                            ->where('supplier_out_of_stock_hidden', false)
                            ->update([
                                'status' => DB::raw('supplier_previous_status'),
                                'supplier_previous_status' => null,
                            ]);
                        $supplier->products()
                            ->whereNotNull('supplier_previous_status')
                            ->where('supplier_out_of_stock_hidden', true)
                            ->update([
                                'status' => 'inactive',
                                'supplier_previous_status' => null,
                            ]);
                    } else {
                        $supplier->products()
                            ->whereNull('supplier_previous_status')
                            ->where('supplier_out_of_stock_hidden', true)
                            ->update([
                                'supplier_previous_status' => 'active',
                                'status' => 'inactive',
                            ]);
                        $supplier->products()
                            ->whereNull('supplier_previous_status')
                            ->where('supplier_out_of_stock_hidden', false)
                            ->update([
                                'supplier_previous_status' => DB::raw('status'),
                                'status' => 'inactive',
                            ]);
                    }
                });
            });
        } catch (SupplierOperationInProgressException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
        if ($productsEnabledChanged) {
            Cache::forget('products_active_storefront');
            Cache::forget('categories_active_storefront');
            Cache::forget('categories_active_storefront_en');
            Cache::forget('categories_active_storefront_vi');
        }

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
        } catch (SupplierOperationInProgressException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
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

    public function balance(string $code)
    {
        try {
            $supplier = SupplierModel::where('code', $code)->firstOrFail();

            if (!$supplier->enabled) {
                return response()->json([
                    'message' => 'Supplier is disabled.',
                ], 422);
            }

            $account = $this->connectorFactory->makeOrderConnector($supplier)->fetchAccount($supplier);

            return response()->json([
                'balance' => $account['balance'],
                'currency' => 'VND',
                'account' => [
                    'id' => $account['id'],
                    'username' => $account['username'],
                    'email' => $account['email'],
                    'level' => $account['level'],
                    'percent' => $account['percent'],
                ],
            ]);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Supplier not found.'], 404);
        } catch (ConnectionException|RequestException $exception) {
            return $this->upstreamError(
                $exception,
                "Supplier balance request failed for [{$code}].",
                'Supplier balance is temporarily unavailable.',
            );
        } catch (RuntimeException $exception) {
            return $this->upstreamError(
                $exception,
                "Supplier balance response failed for [{$code}].",
                'Could not retrieve the supplier balance.',
            );
        } catch (\Throwable $exception) {
            return $this->serverError($exception, "Unexpected supplier balance failure for [{$code}].");
        }
    }

    public function deactivateOutOfStockProducts(string $code)
    {
        $supplier = SupplierModel::where('code', $code)->firstOrFail();

        $activeProducts = ProductModel::where('supplier_id', $supplier->id)
            ->where('status', 'active')
            ->get();

        $deactivatedCount = 0;
        foreach ($activeProducts as $product) {
            $supplierStock = max(0, (int) ($product->supplier_stock ?? 0));
            $localStock = (int) $product->inventoryItems()->where('status', 'available')->count();

            if (($supplierStock + $localStock) <= 0) {
                $product->status = 'inactive';
                $product->supplier_out_of_stock_hidden = true;
                $product->save();
                $deactivatedCount++;
            }
        }

        if ($deactivatedCount > 0) {
            Cache::forget('products_active_storefront');
            Cache::forget('categories_active_storefront');
            Cache::forget('categories_active_storefront_en');
            Cache::forget('categories_active_storefront_vi');
        }

        return response()->json([
            'message' => "Deactivated {$deactivatedCount} out-of-stock products for supplier {$supplier->name}.",
            'count' => $deactivatedCount,
            'supplier' => $this->serialize($supplier->fresh()),
        ]);
    }

    public function activateInStockProducts(string $code)
    {
        $supplier = SupplierModel::where('code', $code)->firstOrFail();

        $inactiveProducts = ProductModel::where('supplier_id', $supplier->id)
            ->where('status', 'inactive')
            ->get();

        $activatedCount = 0;
        foreach ($inactiveProducts as $product) {
            $supplierStock = max(0, (int) ($product->supplier_stock ?? 0));
            $localStock = (int) $product->inventoryItems()->where('status', 'available')->count();

            if (($supplierStock + $localStock) > 0) {
                $product->status = 'active';
                $product->supplier_out_of_stock_hidden = false;
                $product->supplier_previous_status = null;
                $product->save();

                if ($product->category && ! $product->category->is_active) {
                    $product->category->is_active = true;
                    $product->category->save();
                }

                $activatedCount++;
            }
        }

        if ($activatedCount > 0) {
            Cache::forget('products_active_storefront');
            Cache::forget('categories_active_storefront');
            Cache::forget('categories_active_storefront_en');
            Cache::forget('categories_active_storefront_vi');
        }

        return response()->json([
            'message' => "Activated {$activatedCount} in-stock products for supplier {$supplier->name}.",
            'count' => $activatedCount,
            'supplier' => $this->serialize($supplier->fresh()),
        ]);
    }

    public function deactivateOutOfStockCategories(string $code)
    {
        $supplier = SupplierModel::where('code', $code)->firstOrFail();

        $categoryIdsFromProducts = ProductModel::where('supplier_id', $supplier->id)
            ->whereNotNull('category_id')
            ->pluck('category_id');

        $categoryIdsFromCategories = CategoryModel::where('supplier_id', $supplier->id)
            ->pluck('id');

        $categoryIds = $categoryIdsFromProducts->concat($categoryIdsFromCategories)->unique();

        $deactivatedCount = 0;
        foreach ($categoryIds as $catId) {
            $category = CategoryModel::find($catId);
            if (! $category || ! $category->is_active) {
                continue;
            }

            $products = ProductModel::where('category_id', $catId)
                ->where('supplier_id', $supplier->id)
                ->get();

            $totalStock = 0;
            foreach ($products as $product) {
                if ($product->status !== 'active') {
                    continue;
                }
                $supplierStock = max(0, (int) ($product->supplier_stock ?? 0));
                $localStock = (int) $product->inventoryItems()->where('status', 'available')->count();
                $totalStock += ($supplierStock + $localStock);
            }

            if ($totalStock <= 0) {
                $category->is_active = false;
                $category->save();
                $deactivatedCount++;
            }
        }

        if ($deactivatedCount > 0) {
            Cache::forget('categories_active_storefront');
            Cache::forget('products_active_storefront');
            Cache::forget('categories_active_storefront_en');
            Cache::forget('categories_active_storefront_vi');
        }

        return response()->json([
            'message' => "Deactivated {$deactivatedCount} out-of-stock categories for supplier {$supplier->name}.",
            'count' => $deactivatedCount,
            'supplier' => $this->serialize($supplier->fresh()),
        ]);
    }

    private function serialize(SupplierModel $supplier): array
    {
        return [
            'code' => $supplier->code,
            'name' => $supplier->name,
            'base_url' => $supplier->base_url,
            'catalog_driver' => $supplier->catalog_driver,
            'template_enabled' => $supplier->catalog_driver === 'muabm365' ? (bool) config('services.muabm365.enabled') : true,
            'has_api_key' => (bool) ($supplier->api_key ?: config("services.{$supplier->code}.api_key")),
            'enabled' => $supplier->enabled,
            'products_enabled' => $supplier->products_enabled,
            'import_out_of_stock' => $supplier->import_out_of_stock,
            'auto_sync' => $supplier->auto_sync,
            'auto_translate' => (bool) $supplier->auto_translate,
            'target_language' => $supplier->target_language ?: 'vi',
            'sync_interval_minutes' => $supplier->sync_interval_minutes,
            'price_adjustment_type' => $supplier->price_adjustment_type,
            'price_adjustment_value' => $supplier->price_adjustment_value,
            'round_vnd_prices' => (bool) $supplier->round_vnd_prices,
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
