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

class MediaHubConnector implements SupplierCatalogConnector, SupplierOrderConnector
{
    public const DRIVER = 'mediahub';

    public function __construct(private readonly SupplierUrlGuard $urlGuard) {}

    public function fetchCatalog(SupplierModel $supplier): array
    {
        $apiKey = $this->apiKey($supplier);
        $response = $this->baseClient($supplier)
            ->get('/api/v2/telegram-buyer/products', [
                'key' => $apiKey,
            ]);

        $response->throw();
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('MediaHub returned an invalid catalog response.');
        }

        if (isset($payload['success']) && $payload['success'] === false) {
            $msg = (string) ($payload['message'] ?? 'MediaHub error');
            throw new RuntimeException("MediaHub catalog error: {$msg}");
        }

        $products = $payload['products'] ?? $payload['data'] ?? [];
        if (! is_array($products)) {
            throw new RuntimeException('MediaHub returned an invalid products structure.');
        }

        $groupedMap = [];

        foreach ($products as $prod) {
            if (! is_array($prod)) {
                continue;
            }

            $productId = (string) ($prod['_id'] ?? $prod['id'] ?? $prod['product_id'] ?? '');
            if ($productId === '') {
                continue;
            }

            $productName = html_entity_decode((string) ($prod['product_name'] ?? $prod['product_name_raw'] ?? $prod['name'] ?? 'Product'));
            $categoryName = (string) ($prod['categoryName'] ?? $prod['category_name'] ?? $prod['category'] ?? $this->detectCategoryName($productName));
            $categoryId = 'mediahub-cat-'.Str::slug($categoryName);

            $price = (float) ($prod['priceVnd'] ?? $prod['walletPricing'] ?? $prod['pricing'] ?? $prod['price'] ?? 0);
            $quantity = max(0, (int) ($prod['availableQuantity'] ?? $prod['stats']['available'] ?? $prod['quantity'] ?? $prod['amount'] ?? 0));
            $desc = isset($prod['description']) ? trim(html_entity_decode(strip_tags((string) $prod['description']))) : null;

            if (! isset($groupedMap[$categoryId])) {
                $groupedMap[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'children' => [],
                ];
            }

            $groupedMap[$categoryId]['children'][] = [
                'id' => $productId,
                'name' => $productName,
                'price' => $price,
                'quantity' => $quantity,
                'data' => [
                    'description' => $desc,
                ],
            ];
        }

        return array_values($groupedMap);
    }

    private function detectCategoryName(string $name): string
    {
        $upper = mb_strtoupper(trim($name));

        if (str_contains($upper, 'CHATGPT') || str_contains($upper, 'GPT')) {
            return 'CHATGPT / OPENAI';
        }
        if (str_contains($upper, 'CLAUDE')) {
            return 'CLAUDE AI';
        }
        if (str_contains($upper, 'TELEGRAM') || str_contains($upper, 'TGB')) {
            return 'TELEGRAM';
        }
        if (str_contains($upper, 'CANVA')) {
            return 'CANVA PRO';
        }
        if (str_contains($upper, 'NETFLIX')) {
            return 'NETFLIX PREMIUM';
        }
        if (str_contains($upper, 'YOUTUBE')) {
            return 'YOUTUBE PREMIUM';
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
            ->get('/api/v2/telegram-buyer/balance', [
                'key' => $apiKey,
            ]);

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('MediaHub balance request failed with HTTP status '.$response->status());
        }

        if (isset($payload['success']) && $payload['success'] === false) {
            $msg = (string) ($payload['message'] ?? 'API Key không hợp lệ');
            throw new RuntimeException("MediaHub error: {$msg}");
        }

        $balance = $payload['balanceVnd'] ?? $payload['balance'] ?? $payload['balanceUsd'] ?? null;
        if (! is_numeric($balance)) {
            $msg = is_string($payload['message'] ?? null) ? $payload['message'] : 'Không thể lấy số dư tài khoản MediaHub';
            throw new RuntimeException("MediaHub profile error: {$msg}");
        }

        $requester = is_array($payload['requester'] ?? null) ? $payload['requester'] : [];

        return [
            'id' => isset($requester['chatId']) ? (string) $requester['chatId'] : null,
            'username' => isset($requester['name']) ? (string) $requester['name'] : null,
            'email' => null,
            'level' => null,
            'percent' => null,
            'balance' => (float) $balance,
        ];
    }

    public function purchase(SupplierModel $supplier, string $externalProductId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new RuntimeException('Supplier purchase quantity must be at least one.');
        }

        $apiKey = $this->apiKey($supplier);
        $idempotencyKey = 'purchase-'.date('YmdHis').'-'.Str::lower(Str::random(8));

        $response = $this->baseClient($supplier, false)
            ->withHeaders([
                'Idempotency-Key' => $idempotencyKey,
            ])
            ->post('/api/v2/telegram-buyer/purchase', [
                'key' => $apiKey,
                'product_id' => $externalProductId,
                'quantity' => $quantity,
            ]);

        $payload = $response->json();
        $this->throwPurchaseFailure($response->status(), $payload);

        if (! is_array($payload)) {
            throw new RuntimeException('MediaHub returned an invalid purchase response.');
        }

        if (isset($payload['success']) && $payload['success'] === false) {
            $msg = (string) ($payload['message'] ?? 'Purchase failed');
            $this->throwPurchaseFailure(400, ['message' => $msg]);
        }

        $orderId = (string) ($payload['orderCode'] ?? $payload['order_id'] ?? $payload['trans_id'] ?? $idempotencyKey);
        $resources = $this->extractResources($payload['deliveredAccounts'] ?? $payload['data'] ?? []);

        return [
            'order_id' => $orderId,
            'resources' => $resources,
            'raw' => $payload,
        ];
    }

    public function fetchOrder(SupplierModel $supplier, string $supplierOrderId): array
    {
        return [
            'order_id' => $supplierOrderId,
            'resources' => [],
            'raw' => ['orderCode' => $supplierOrderId],
        ];
    }

    public function detectDriver(SupplierModel $supplier): string
    {
        $this->urlGuard->normalizeAndValidate($supplier->base_url);

        if (trim((string) $supplier->api_key) === '') {
            return self::DRIVER;
        }

        try {
            $this->fetchAccount($supplier);

            return self::DRIVER;
        } catch (Throwable $exception) {
            throw new RuntimeException('The supplier URL or API key is invalid for MediaHub: '.$exception->getMessage());
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
                'verify' => false,
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
            ? (string) ($payload['message'] ?? $payload['msg'] ?? $payload['error'] ?? '')
            : '';
        $normalized = mb_strtolower($message);

        if (str_contains($normalized, 'hết')
            || str_contains($normalized, 'không đủ')
            || str_contains($normalized, 'out of stock')
            || str_contains($normalized, 'insufficient stock')
            || str_contains($normalized, 'not enough')) {
            throw new SupplierOutOfStockException($message ?: 'MediaHub is out of stock.');
        }

        if ($status >= 400 || (is_array($payload) && isset($payload['success']) && $payload['success'] === false)) {
            throw new SupplierPurchaseRejectedException($message ?: 'MediaHub purchase failed.');
        }
    }

    private function extractResources(mixed $data): array
    {
        if (is_array($data)) {
            $list = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $parts = [];
                    if (! empty($item['user'])) {
                        $parts[] = trim((string) $item['user']);
                    }
                    if (! empty($item['password'])) {
                        $parts[] = trim((string) $item['password']);
                    }
                    if (! empty($item['verifyEmail'])) {
                        $parts[] = trim((string) $item['verifyEmail']);
                    }
                    if (! empty($parts)) {
                        $list[] = implode('|', $parts);
                    }
                } elseif (is_string($item) && trim($item) !== '') {
                    $list[] = trim($item);
                }
            }

            return array_values(array_unique($list));
        }

        if (is_string($data) && trim($data) !== '') {
            return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data) ?: [])));
        }

        return [];
    }
}
