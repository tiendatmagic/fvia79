<?php

namespace App\Integrations\Suppliers;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\Contracts\SupplierCatalogConnector;
use App\Integrations\Suppliers\Contracts\SupplierOrderConnector;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class Muabm365Connector implements SupplierCatalogConnector, SupplierOrderConnector
{
    public const DRIVER = 'muabm365';

    public function __construct(private readonly SupplierUrlGuard $urlGuard) {}

    public function fetchCatalog(SupplierModel $supplier): array
    {
        $apiKey = $this->apiKey($supplier);
        $response = $this->baseClient($supplier)
            ->get('/api.php', [
                'action' => 'get-services',
                'apikey' => $apiKey,
            ]);

        $response->throw();
        $payload = $response->json();

        if (! is_array($payload) || ($payload['code'] ?? null) !== 0 || ! is_array($payload['data'] ?? null)) {
            $msg = is_array($payload) ? ($payload['message'] ?? 'Invalid response') : 'Invalid response format';
            throw new RuntimeException("MUABM365 catalog error: {$msg}");
        }

        $services = $payload['data'];
        $grouped = [];

        foreach ($services as $product) {
            $serviceName = (string) ($product['service_name'] ?? "Service {$product['service_id']}");
            $categoryName = $this->detectCategoryName($serviceName);
            $categoryId = 'muabm365-cat-'.Str::slug($categoryName);

            if (! isset($grouped[$categoryId])) {
                $grouped[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'children' => [],
                ];
            }

            $desc = isset($product['description']) ? trim((string) $product['description']) : '';
            $warranty = isset($product['warranty_policy']) ? trim((string) $product['warranty_policy']) : '';
            $fullDescription = $desc;
            if ($warranty !== '') {
                $fullDescription = $fullDescription !== '' ? "{$fullDescription}\n\n{$warranty}" : $warranty;
            }

            $grouped[$categoryId]['children'][] = [
                'id' => (string) ($product['service_id'] ?? ''),
                'name' => $serviceName,
                'price' => (float) ($product['price'] ?? 0),
                'quantity' => max(0, (int) ($product['quantity'] ?? 0)),
                'data' => [
                    'description' => $fullDescription !== '' ? $fullDescription : null,
                ],
            ];
        }

        return array_values($grouped);
    }

    private function detectCategoryName(string $name): string
    {
        $upper = mb_strtoupper(trim($name));

        if (str_starts_with($upper, 'BM50')) {
            return 'BM50';
        }
        if (str_starts_with($upper, 'BM350')) {
            return 'BM350';
        }
        if (str_starts_with($upper, 'BM5') || str_starts_with($upper, 'XÁC BM5')) {
            return 'BM5';
        }
        if (str_starts_with($upper, 'BM3')) {
            return 'BM3';
        }
        if (str_starts_with($upper, 'BM1')) {
            return 'BM1';
        }
        if (str_starts_with($upper, 'BM NGÂM') || str_starts_with($upper, 'BM FULL')) {
            return 'BM NGÂM';
        }
        if (str_starts_with($upper, 'COMBO 1 ACCOUNT') || str_starts_with($upper, 'ACCOUNT')) {
            return 'ACCOUNT FB';
        }
        if (str_starts_with($upper, 'CLONE IG')) {
            return 'CLONE IG';
        }
        if (str_starts_with($upper, 'HOTMAIL')) {
            return 'HOTMAIL';
        }

        $parts = explode(' ', trim($name));

        return count($parts) > 1 ? implode(' ', array_slice($parts, 0, 2)) : $name;
    }

    public function fetchBalance(SupplierModel $supplier): float
    {
        return $this->fetchAccount($supplier)['balance'];
    }

    public function fetchAccount(SupplierModel $supplier): array
    {
        $apiKey = $this->apiKey($supplier);
        $response = $this->baseClient($supplier)
            ->get('/api.php', [
                'action' => 'get-balance',
                'apikey' => $apiKey,
            ]);

        $response->throw();
        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload['balance']) || ! is_numeric($payload['balance'])) {
            $msg = is_array($payload) ? ($payload['message'] ?? 'Invalid balance response') : 'Invalid response format';
            throw new RuntimeException("MUABM365 balance error: {$msg}");
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

    public function purchase(SupplierModel $supplier, string $externalProductId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new RuntimeException('Supplier purchase quantity must be at least one.');
        }

        $apiKey = $this->apiKey($supplier);
        $response = $this->baseClient($supplier, false)
            ->get('/api.php', [
                'action' => 'create-order',
                'apikey' => $apiKey,
                'service_id' => $externalProductId,
                'amount' => $quantity,
            ]);

        $payload = $response->json();
        $this->throwPurchaseFailure($response->status(), $payload);

        if (! is_array($payload)) {
            throw new RuntimeException('MUABM365 returned an invalid purchase response.');
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        if ($orderId === '') {
            throw new RuntimeException('MUABM365 purchase response is missing order_id.');
        }

        $resources = $this->extractResources($payload['data'] ?? []);

        return [
            'order_id' => $orderId,
            'resources' => $resources,
            'raw' => $payload,
        ];
    }

    public function fetchOrder(SupplierModel $supplier, string $supplierOrderId): array
    {
        $apiKey = $this->apiKey($supplier);
        $response = $this->baseClient($supplier)
            ->get('/api.php', [
                'action' => 'get-order-detail',
                'apikey' => $apiKey,
                'order_id' => $supplierOrderId,
            ]);

        $response->throw();
        $payload = $response->json();

        if (! is_array($payload) || ($payload['code'] ?? null) !== 0 || ! is_array($payload['order'] ?? null)) {
            throw new RuntimeException('MUABM365 returned an invalid order detail response.');
        }

        $order = $payload['order'];
        $resources = $this->extractResources($order['data'] ?? []);

        return [
            'order_id' => (string) ($order['order_id'] ?? $supplierOrderId),
            'resources' => $resources,
            'raw' => $payload,
        ];
    }

    public function detectDriver(SupplierModel $supplier): string
    {
        $this->urlGuard->normalizeAndValidate($supplier->base_url);

        try {
            $this->fetchAccount($supplier);

            return self::DRIVER;
        } catch (Throwable $exception) {
            throw new RuntimeException('The supplier URL or API key is invalid for MUABM365: '.$exception->getMessage());
        }
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
        $apiKey = trim((string) $supplier->api_key);
        if ($apiKey === '') {
            throw new RuntimeException('Supplier API key has not been configured.');
        }

        return $apiKey;
    }

    private function throwPurchaseFailure(int $status, mixed $payload): void
    {
        $message = is_array($payload)
            ? (string) ($payload['message'] ?? $payload['error'] ?? $payload['msg'] ?? '')
            : '';
        $code = is_array($payload) ? ($payload['code'] ?? null) : null;
        $normalized = mb_strtolower($message);

        if (str_contains($normalized, 'hết')
            || str_contains($normalized, 'không đủ')
            || str_contains($normalized, 'out of stock')
            || str_contains($normalized, 'insufficient stock')) {
            throw new SupplierOutOfStockException($message ?: 'MUABM365 is out of stock.');
        }

        if ($status >= 400 || ($code !== 200 && $code !== 0 && $code !== null)) {
            throw new SupplierPurchaseRejectedException($message ?: 'MUABM365 purchase failed.');
        }
    }

    private function extractResources(mixed $data): array
    {
        if (is_array($data)) {
            return array_values(array_filter(array_map('trim', array_map(static fn ($item): string => (string) $item, $data))));
        }
        if (is_string($data) && trim($data) !== '') {
            return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data) ?: [])));
        }

        return [];
    }
}
