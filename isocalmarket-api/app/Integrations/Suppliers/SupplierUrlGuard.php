<?php

namespace App\Integrations\Suppliers;

use InvalidArgumentException;

class SupplierUrlGuard
{
    public function normalizeAndValidate(string $url, bool $resolveDns = true): string
    {
        $url = rtrim(trim($url), '/');
        $parts = parse_url($url);

        if (($parts['scheme'] ?? null) !== 'https'
            || empty($parts['host'])
            || isset($parts['user'], $parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            throw new InvalidArgumentException('Supplier URL must be a plain HTTPS origin without credentials, query, fragment, or custom port.');
        }

        $host = strtolower($parts['host']);
        if (! $resolveDns) {
            return "https://{$host}";
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : $this->resolveAddresses($host);

        if ($addresses === []) {
            throw new InvalidArgumentException('Supplier hostname could not be resolved.');
        }

        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new InvalidArgumentException('Supplier URL must resolve only to public internet addresses.');
            }
        }

        return "https://{$host}";
    }

    private function resolveAddresses(string $host): array
    {
        $records = dns_get_record($host, DNS_A | DNS_AAAA) ?: [];

        return array_values(array_unique(array_filter(array_map(
            static fn (array $record): ?string => $record['ip'] ?? $record['ipv6'] ?? null,
            $records,
        ))));
    }
}
