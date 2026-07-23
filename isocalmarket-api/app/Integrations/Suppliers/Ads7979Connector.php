<?php

namespace App\Integrations\Suppliers;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\Contracts\SupplierCatalogConnector;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Ads7979Connector implements SupplierCatalogConnector
{
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
        $response = $this->client($supplier)->post('/api/v2/me');
        $response->throw();
        $payload = $response->json();
        $balance = is_array($payload) ? ($payload['balance'] ?? $payload['data']['balance'] ?? null) : null;

        if (! is_numeric($balance)) {
            throw new RuntimeException('ADS7979 returned an invalid account response.');
        }

        return (float) $balance;
    }

    private function client(SupplierModel $supplier): PendingRequest
    {
        $apiKey = $supplier->api_key ?: config('services.ads7979.api_key');
        if (! $apiKey) {
            throw new RuntimeException('ADS7979 API key has not been configured.');
        }

        return Http::baseUrl(rtrim($supplier->base_url, '/'))
            ->acceptJson()
            ->withToken($apiKey)
            ->timeout(30)
            ->retry(2, 250);
    }
}
