<?php

namespace App\Integrations\Suppliers;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\Contracts\SupplierCatalogConnector;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CloneStoreConnector implements SupplierCatalogConnector
{
    public const V1 = 'clone_store_v1';

    public const V2 = 'clone_store_v2';

    public function __construct(private readonly SupplierUrlGuard $urlGuard) {}

    public function fetchCatalog(SupplierModel $supplier): array
    {
        $this->urlGuard->normalizeAndValidate($supplier->base_url, false);

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
        $this->urlGuard->normalizeAndValidate($supplier->base_url, false);

        return match ($supplier->catalog_driver) {
            self::V1 => $this->fetchV1Account($supplier),
            self::V2 => $this->fetchV2Account($supplier),
            default => throw new RuntimeException("Unsupported supplier catalog driver: {$supplier->catalog_driver}"),
        };
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

    private function authenticatedClient(SupplierModel $supplier): PendingRequest
    {
        return $this->baseClient($supplier)->withToken($this->apiKey($supplier));
    }

    private function baseClient(SupplierModel $supplier): PendingRequest
    {
        return Http::baseUrl(rtrim($supplier->base_url, '/'))
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 250);
    }

    private function apiKey(SupplierModel $supplier): string
    {
        $apiKey = $supplier->api_key ?: config("services.{$supplier->code}.api_key");
        if (! $apiKey) {
            throw new RuntimeException('Supplier API key has not been configured.');
        }

        return $apiKey;
    }
}
