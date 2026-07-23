<?php

namespace App\Integrations\Suppliers;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\Contracts\SupplierCatalogConnector;
use App\Integrations\Suppliers\Contracts\SupplierOrderConnector;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CloneStoreConnector implements SupplierCatalogConnector, SupplierOrderConnector
{
    public const V1 = 'clone_store_v1';

    public const V2 = 'clone_store_v2';

    public function __construct(private readonly SupplierUrlGuard $urlGuard) {}

    public function fetchCatalog(SupplierModel $supplier): array
    {
        return match ($supplier->catalog_driver) {
            self::V1 => $this->fetchV1($supplier),
            self::V2 => $this->fetchV2($supplier),
            default => throw new RuntimeException("Unsupported supplier catalog driver: {$supplier->catalog_driver}"),
        };
    }

    public function fetchBalance(SupplierModel $supplier): float
    {
        return $this->fetchAccount($supplier)['balance'];
    }

    public function fetchAccount(SupplierModel $supplier): array
    {
        return match ($supplier->catalog_driver) {
            self::V1 => $this->fetchV1Account($supplier),
            self::V2 => $this->fetchV2Account($supplier),
            default => throw new RuntimeException("Unsupported supplier catalog driver: {$supplier->catalog_driver}"),
        };
    }

    public function purchase(SupplierModel $supplier, string $externalProductId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new RuntimeException('Supplier purchase quantity must be at least one.');
        }

        $response = match ($supplier->catalog_driver) {
            self::V1 => $this->baseClient($supplier, false)
                ->asForm()
                ->post('/api/v1/buy', [
                    'api_key' => $this->apiKey($supplier),
                    'id_product' => $externalProductId,
                    'quantity' => $quantity,
                ]),
            self::V2 => $this->authenticatedClient($supplier, false)
                ->post('/api/v2/order/create', [
                    'category_id' => $externalProductId,
                    'quantity' => $quantity,
                ]),
            default => throw new RuntimeException("Unsupported supplier order driver: {$supplier->catalog_driver}"),
        };

        $this->throwPurchaseFailure($response->status(), $response->json());
        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Supplier returned an invalid purchase response.');
        }

        $orderId = $this->extractOrderId($payload);
        if ($orderId === null) {
            throw new RuntimeException('Supplier purchase response is missing its order id.');
        }

        $result = $this->normalizeOrder($payload, $orderId);
        if (count($result['resources']) < $quantity) {
            try {
                $result = $this->fetchOrder($supplier, $orderId);
            } catch (Throwable $exception) {
                throw new SupplierOrderPendingException(
                    $orderId,
                    $payload,
                    previous: $exception,
                );
            }
        }
        if (count($result['resources']) < $quantity) {
            throw new SupplierOrderPendingException($orderId, $result['raw']);
        }

        return $result;
    }

    public function fetchOrder(SupplierModel $supplier, string $supplierOrderId): array
    {
        $response = match ($supplier->catalog_driver) {
            self::V1 => $this->baseClient($supplier)
                ->asForm()
                ->post('/api/v1/order', [
                    'api_key' => $this->apiKey($supplier),
                    'order_id' => $supplierOrderId,
                ]),
            self::V2 => $this->authenticatedClient($supplier)
                ->post('/api/v2/order/detail', ['id' => $supplierOrderId]),
            default => throw new RuntimeException("Unsupported supplier order driver: {$supplier->catalog_driver}"),
        };

        $response->throw();
        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Supplier returned an invalid order response.');
        }

        return $this->normalizeOrder($payload, $supplierOrderId);
    }

    public function detectDriver(SupplierModel $supplier): string
    {
        $this->urlGuard->normalizeAndValidate($supplier->base_url);
        $errors = [];

        try {
            $this->fetchV2($supplier);

            return self::V2;
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        try {
            $this->validateV1Key($supplier);
            $this->fetchV1($supplier);

            return self::V1;
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        throw new RuntimeException('The supplier does not support the compatible Clone Store API V1/V2, or its API key is invalid.');
    }

    private function fetchV2(SupplierModel $supplier): array
    {
        $response = $this->authenticatedClient($supplier)
            ->post('/api/v2/categories');
        $response->throw();

        return $this->normalizeV2($response->json());
    }

    private function fetchV1(SupplierModel $supplier): array
    {
        $response = $this->baseClient($supplier)->get('/api/v1/categories');
        $response->throw();
        $payload = $response->json();
        $categories = is_array($payload) ? ($payload['data'] ?? null) : null;

        if (! is_array($categories)) {
            throw new RuntimeException('Supplier returned an invalid V1 catalog response.');
        }

        return array_map(static fn (array $category): array => [
            'id' => $category['id'] ?? null,
            'name' => $category['name'] ?? null,
            'image' => $category['icon'] ?? null,
            'children' => array_map(static fn (array $product): array => [
                'id' => $product['id_product'] ?? null,
                'name' => $product['name'] ?? null,
                'price' => $product['price'] ?? 0,
                'quantity' => $product['quantity'] ?? 0,
                'data' => [
                    'description' => $product['desc'] ?? null,
                    'hdsd' => $product['hdsd'] ?? null,
                ],
            ], is_array($category['list_product'] ?? null) ? $category['list_product'] : []),
        ], $categories);
    }

    private function fetchV1Account(SupplierModel $supplier): array
    {
        $response = $this->baseClient($supplier)
            ->asForm()
            ->post('/api/v1/balance', ['api_key' => $this->apiKey($supplier)]);
        $response->throw();
        $payload = $response->json();

        if (! is_array($payload) || ($payload['status'] ?? null) !== true || ! is_numeric($payload['balance'] ?? null)) {
            throw new RuntimeException('Supplier returned an invalid V1 balance response.');
        }

        return [
            'id' => null,
            'username' => null,
            'email' => null,
            'level' => null,
            'percent' => null,
            'balance' => (float) $payload['balance'],
        ];
    }

    private function fetchV2Account(SupplierModel $supplier): array
    {
        $response = $this->authenticatedClient($supplier)->post('/api/v2/me');
        $response->throw();
        $account = $this->findAccount($response->json());

        if ($account === null) {
            throw new RuntimeException('Supplier returned an invalid V2 account response.');
        }

        return [
            'id' => $account['id'] ?? null,
            'username' => isset($account['username']) ? (string) $account['username'] : null,
            'email' => isset($account['email']) ? (string) $account['email'] : null,
            'level' => isset($account['level']) && is_numeric($account['level']) ? (int) $account['level'] : null,
            'percent' => isset($account['percent']) && is_numeric($account['percent']) ? (float) $account['percent'] : null,
            'balance' => (float) ($account['balance'] ?? $account['wallet_balance']),
        ];
    }

    private function validateV1Key(SupplierModel $supplier): void
    {
        $response = $this->baseClient($supplier)
            ->asForm()
            ->post('/api/v1/balance', ['api_key' => $this->apiKey($supplier)]);
        $response->throw();

        if ($response->json('status') !== true) {
            throw new RuntimeException('Supplier rejected the V1 API key.');
        }
    }

    private function findAccount(mixed $payload): ?array
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach (['balance', 'wallet_balance'] as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return $payload;
            }
        }

        foreach (['data', 'user', 'account'] as $key) {
            $account = $this->findAccount($payload[$key] ?? null);
            if ($account !== null) {
                return $account;
            }
        }

        return null;
    }

    private function normalizeV2(mixed $payload): array
    {
        $catalog = is_array($payload) && isset($payload['data']) && is_array($payload['data'])
            ? $payload['data']
            : $payload;

        if (! is_array($catalog) || ! array_is_list($catalog)) {
            throw new RuntimeException('Supplier returned an invalid V2 catalog response.');
        }

        foreach ($catalog as $category) {
            if (! is_array($category) || ! array_key_exists('id', $category) || ! is_array($category['children'] ?? null)) {
                throw new RuntimeException('Supplier returned an incompatible V2 catalog schema.');
            }
        }

        return $catalog;
    }

    private function authenticatedClient(SupplierModel $supplier, bool $retry = true): PendingRequest
    {
        return $this->baseClient($supplier, $retry)->withToken($this->apiKey($supplier));
    }

    private function baseClient(SupplierModel $supplier, bool $retry = true): PendingRequest
    {
        $target = $this->urlGuard->validatedConnection($supplier->base_url);
        if (! defined('CURLOPT_RESOLVE')) {
            throw new RuntimeException('The cURL extension is required for secure supplier connections.');
        }
        $address = $target['addresses'][0];
        $curlAddress = str_contains($address, ':') ? "[{$address}]" : $address;

        $client = Http::baseUrl($target['url'])
            ->withOptions([
                'allow_redirects' => false,
                'protocols' => ['https'],
                'curl' => [
                    CURLOPT_RESOLVE => ["{$target['host']}:443:{$curlAddress}"],
                ],
            ])
            ->acceptJson()
            ->timeout(30);

        return $retry ? $client->retry(2, 250) : $client;
    }

    private function apiKey(SupplierModel $supplier): string
    {
        $apiKey = $supplier->api_key ?: config("services.{$supplier->code}.api_key");
        if (! $apiKey) {
            throw new RuntimeException('Supplier API key has not been configured.');
        }

        return $apiKey;
    }

    private function throwPurchaseFailure(int $status, mixed $payload): void
    {
        $message = is_array($payload)
            ? (string) ($payload['message'] ?? $payload['error'] ?? $payload['msg'] ?? '')
            : '';
        $normalized = mb_strtolower($message);

        if (str_contains($normalized, 'hết')
            || str_contains($normalized, 'không đủ hàng')
            || str_contains($normalized, 'không đủ số lượng')
            || str_contains($normalized, 'out of stock')
            || str_contains($normalized, 'insufficient stock')) {
            throw new SupplierOutOfStockException($message ?: 'Supplier is out of stock.');
        }

        if ($status >= 400 || (is_array($payload) && ($payload['status'] ?? true) === false)) {
            throw new SupplierPurchaseRejectedException($message ?: 'Supplier purchase failed.');
        }
    }

    private function extractOrderId(array $payload): ?string
    {
        foreach (['order_id', 'id_order', 'order_code'] as $key) {
            if (isset($payload[$key]) && is_scalar($payload[$key])) {
                return (string) $payload[$key];
            }
        }

        foreach (['data', 'order', 'result', 'detail'] as $container) {
            if (is_array($payload[$container] ?? null)) {
                $orderId = $this->extractOrderId($payload[$container]);
                if ($orderId !== null) {
                    return $orderId;
                }
            }
        }

        if (isset($payload['id']) && is_scalar($payload['id'])) {
            return (string) $payload['id'];
        }

        return null;
    }

    /**
     * @return array{order_id: string, resources: array<int, string>, raw: array}
     */
    private function normalizeOrder(array $payload, string $orderId): array
    {
        return [
            'order_id' => $orderId,
            'resources' => $this->extractResources($payload),
            'raw' => $payload,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractResources(mixed $payload): array
    {
        if (! is_array($payload)) {
            return is_string($payload) && trim($payload) !== ''
                ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $payload) ?: [])))
                : [];
        }
        if (array_is_list($payload)) {
            return $this->normalizeResourceList($payload);
        }

        foreach ([
            'accounts', 'keys', 'data_export', 'export_data', 'order_data',
            'product_data', 'data_product', 'products', 'items', 'list_data',
        ] as $key) {
            if (array_key_exists($key, $payload)) {
                $resources = $this->normalizeResourceList($payload[$key]);
                if ($resources !== []) {
                    return $resources;
                }
            }
        }

        foreach (['data', 'order', 'result', 'detail', 'order_detail'] as $key) {
            if (array_key_exists($key, $payload)) {
                $resources = $this->extractResources($payload[$key]);
                if ($resources !== []) {
                    return $resources;
                }
            }
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function normalizeResourceList(mixed $value): array
    {
        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: [])));
        }
        if (! is_array($value)) {
            return [];
        }
        if (! array_is_list($value)) {
            $nested = $this->extractResources($value);

            return $nested !== [] ? $nested : [json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => is_scalar($item)
                ? trim((string) $item)
                : json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $value,
        ), static fn (string $item): bool => $item !== ''));
    }
}
