<?php

namespace App\Integrations\Suppliers;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\Contracts\SupplierCatalogConnector;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Ads7979Connector implements SupplierCatalogConnector
{
    public function __construct(private readonly SupplierUrlGuard $urlGuard) {}

    public function fetchCatalog(SupplierModel $supplier): array
    {
        $response = $this->client($supplier)->post('/api/v2/categories');
        $response->throw();
        $catalog = $response->json();

        if (! is_array($catalog)) {
            throw new RuntimeException('ADS7979 returned an invalid catalog response.');
        }

        return $catalog;
    }

    public function fetchBalance(SupplierModel $supplier): float
    {
        return $this->fetchAccount($supplier)['balance'];
    }

    public function fetchAccount(SupplierModel $supplier): array
    {
        $response = $this->client($supplier)->post('/api/v2/me');
        $response->throw();
        $payload = $response->json();
        $account = is_array($payload) && is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $balance = is_array($account) ? ($account['balance'] ?? null) : null;

        if (! is_numeric($balance)) {
            throw new RuntimeException('ADS7979 returned an invalid account response.');
        }

        return [
            'id' => $account['id'] ?? null,
            'username' => isset($account['username']) ? (string) $account['username'] : null,
            'email' => isset($account['email']) ? (string) $account['email'] : null,
            'level' => isset($account['level']) && is_numeric($account['level']) ? (int) $account['level'] : null,
            'percent' => isset($account['percent']) && is_numeric($account['percent']) ? (float) $account['percent'] : null,
            'balance' => (float) $balance,
        ];
    }

    private function client(SupplierModel $supplier): PendingRequest
    {
        $apiKey = $supplier->api_key ?: config('services.ads7979.api_key');
        if (! $apiKey) {
            throw new RuntimeException('ADS7979 API key has not been configured.');
        }

        $target = $this->urlGuard->validatedConnection($supplier->base_url);
        if (! defined('CURLOPT_RESOLVE')) {
            throw new RuntimeException('The cURL extension is required for secure supplier connections.');
        }
        $address = $target['addresses'][0];
        $curlAddress = str_contains($address, ':') ? "[{$address}]" : $address;

        return Http::baseUrl($target['url'])
            ->withOptions([
                'allow_redirects' => false,
                'protocols' => ['https'],
                'curl' => [
                    CURLOPT_RESOLVE => ["{$target['host']}:443:{$curlAddress}"],
                ],
            ])
            ->acceptJson()
            ->withToken($apiKey)
            ->timeout(30)
            ->retry(2, 250);
    }
}
