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

class ClonetutConnector implements SupplierCatalogConnector, SupplierOrderConnector
{
    public const DRIVER = 'clonetut';

    public function __construct(private readonly SupplierUrlGuard $urlGuard) {}

    public function fetchCatalog(SupplierModel $supplier): array
    {
        $apiKey = $this->apiKey($supplier);
        $response = $this->baseClient($supplier)
            ->get('/api/products.php', [
                'api_key' => $apiKey,
            ]);

        $response->throw();
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Clonetut returned an invalid catalog response.');
        }

        $categories = $payload['categories'] ?? $payload['data'] ?? [];
        if (! is_array($categories)) {
            throw new RuntimeException('Clonetut returned an invalid catalog structure.');
        }

        $groupedMap = [];

        foreach ($categories as $cat) {
            if (! is_array($cat)) {
                continue;
            }

            if (isset($cat['products']) && is_array($cat['products'])) {
                $categoryName = (string) ($cat['name'] ?? 'Chuyên Mục Clonetut');
                $categoryId = 'clonetut-cat-'.($cat['id'] ?? Str::slug($categoryName));

                $children = [];
                foreach ($cat['products'] as $prod) {
                    if (! is_array($prod)) {
                        continue;
                    }
                    $productId = (string) ($prod['id'] ?? '');
                    $productName = html_entity_decode((string) ($prod['name'] ?? 'Product'));
                    $price = (float) ($prod['price'] ?? 0);
                    $quantity = max(0, (int) ($prod['amount'] ?? $prod['quantity'] ?? $prod['stock'] ?? 0));
                    $desc = isset($prod['description']) ? trim(html_entity_decode(strip_tags((string) $prod['description']))) : null;

                    $children[] = [
                        'id' => $productId,
                        'name' => $productName,
                        'price' => $price,
                        'quantity' => $quantity,
                        'data' => [
                            'description' => $desc,
                        ],
                    ];
                }

                if (! empty($children)) {
                    $groupedMap[$categoryId] = [
                        'id' => $categoryId,
                        'name' => $categoryName,
                        'children' => $children,
                    ];
                }
            } else {
                $productName = html_entity_decode((string) ($cat['name'] ?? $cat['product_name'] ?? 'Product'));
                $categoryName = (string) ($cat['category_name'] ?? $cat['category'] ?? $this->detectCategoryName($productName));
                $categoryId = 'clonetut-cat-'.Str::slug($categoryName);

                if (! isset($groupedMap[$categoryId])) {
                    $groupedMap[$categoryId] = [
                        'id' => $categoryId,
                        'name' => $categoryName,
                        'children' => [],
                    ];
                }

                $productId = (string) ($cat['id'] ?? $cat['product_id'] ?? '');
                $price = (float) ($cat['price'] ?? 0);
                $quantity = max(0, (int) ($cat['amount'] ?? $cat['quantity'] ?? 0));
                $desc = isset($cat['description']) ? trim(html_entity_decode(strip_tags((string) $cat['description']))) : null;

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
        }

        return array_values($groupedMap);
    }

    private function detectCategoryName(string $name): string
    {
        $upper = mb_strtoupper(trim($name));

        if (str_contains($upper, 'CLONE')) {
            return 'CLONE FACEBOOK';
        }
        if (str_contains($upper, 'VIA')) {
            return 'VIA FACEBOOK';
        }
        if (str_contains($upper, 'BM')) {
            return 'BUSINESS MANAGER';
        }
        if (str_contains($upper, 'HOTMAIL') || str_contains($upper, 'MAIL')) {
            return 'EMAIL HOTMAIL';
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
            ->get('/api/profile.php', [
                'api_key' => $apiKey,
            ]);

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Clonetut profile request failed with HTTP status '.$response->status());
        }

        if (($payload['status'] ?? null) === 'error' || ($payload['status'] ?? null) === false) {
            $msg = (string) ($payload['msg'] ?? $payload['message'] ?? 'API Key không hợp lệ');
            throw new RuntimeException("Clonetut error: {$msg}");
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $balance = $data['money'] ?? $data['balance'] ?? $data['coin'] ?? null;

        if (! is_numeric($balance)) {
            $msg = is_string($payload['msg'] ?? $payload['message'] ?? null) ? ($payload['msg'] ?? $payload['message']) : 'Không thể lấy số dư tài khoản Clonetut';
            throw new RuntimeException("Clonetut profile error: {$msg}");
        }

        return [
            'id' => isset($data['id']) ? (string) $data['id'] : null,
            'username' => isset($data['username']) ? (string) $data['username'] : null,
            'email' => isset($data['email']) ? (string) $data['email'] : null,
            'level' => isset($data['level']) && is_numeric($data['level']) ? (int) $data['level'] : null,
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
        $response = $this->baseClient($supplier, false)
            ->asForm()
            ->post('/api/buy_product', [
                'action' => 'buyProduct',
                'id' => $externalProductId,
                'amount' => $quantity,
                'coupon' => '',
                'api_key' => $apiKey,
            ]);

        $payload = $response->json();
        $this->throwPurchaseFailure($response->status(), $payload);

        if (! is_array($payload)) {
            throw new RuntimeException('Clonetut returned an invalid purchase response.');
        }

        $status = $payload['status'] ?? null;
        if ($status !== 'success' && $status !== true && ($payload['code'] ?? null) !== 0 && ($payload['code'] ?? null) !== 200) {
            $msg = (string) ($payload['msg'] ?? $payload['message'] ?? $payload['error'] ?? 'Purchase failed');
            $this->throwPurchaseFailure(400, ['msg' => $msg]);
        }

        $orderId = (string) ($payload['trans_id'] ?? $payload['order_id'] ?? $payload['trans_code'] ?? '');
        if ($orderId === '') {
            throw new RuntimeException('Clonetut purchase response is missing trans_id/order_id.');
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
            ->get('/api/order.php', [
                'api_key' => $apiKey,
                'order' => $supplierOrderId,
            ]);

        $response->throw();
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Clonetut returned an invalid order detail response.');
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $resources = $this->extractResources($data['accounts'] ?? $data['data'] ?? $data['list'] ?? $data);

        return [
            'order_id' => (string) ($data['trans_id'] ?? $data['order_id'] ?? $supplierOrderId),
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
            throw new RuntimeException('The supplier URL or API key is invalid for Clonetut: '.$exception->getMessage());
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
            ? (string) ($payload['msg'] ?? $payload['message'] ?? $payload['error'] ?? '')
            : '';
        $normalized = mb_strtolower($message);

        if (str_contains($normalized, 'hết')
            || str_contains($normalized, 'không đủ')
            || str_contains($normalized, 'out of stock')
            || str_contains($normalized, 'insufficient stock')) {
            throw new SupplierOutOfStockException($message ?: 'Clonetut is out of stock.');
        }

        if ($status >= 400 || (is_array($payload) && isset($payload['status']) && ($payload['status'] === 'error' || $payload['status'] === false))) {
            throw new SupplierPurchaseRejectedException($message ?: 'Clonetut purchase failed.');
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
