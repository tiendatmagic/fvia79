<?php

namespace App\Integrations\Suppliers;

use InvalidArgumentException;

class SupplierUrlGuard
{
    public function normalizeAndValidate(string $url, bool $resolveDns = true): string
    {
        $target = $this->normalize($url);
        if ($resolveDns) {
            $this->resolvePublicAddresses($target['host']);
        }

        return $target['url'];
    }

    /**
     * @return array{url: string, host: string, addresses: array<int, string>}
     */
    public function validatedConnection(string $url): array
    {
        $target = $this->normalize($url);

        return [
            ...$target,
            'addresses' => $this->resolvePublicAddresses($target['host']),
        ];
    }

    /**
     * @return array{url: string, host: string}
     */
    private function normalize(string $url): array
    {
        $url = rtrim(trim($url), '/');
        $parts = parse_url($url);

        if (($parts['scheme'] ?? null) !== 'https'
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
            || (isset($parts['path']) && $parts['path'] !== '')
            || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            throw new InvalidArgumentException('Supplier URL must be a plain HTTPS origin without credentials, query, fragment, or custom port.');
        }

        $host = trim(strtolower($parts['host']), '[]');
        $urlHost = str_contains($host, ':') ? "[{$host}]" : $host;

        return ['url' => "https://{$urlHost}", 'host' => $host];
    }

    /**
     * @return array<int, string>
     */
    private function resolvePublicAddresses(string $host): array
    {
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

        return $addresses;
    }

    private function resolveAddresses(string $host): array
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];

        return array_values(array_unique(array_filter(array_map(
            static fn (array $record): ?string => $record['ip'] ?? $record['ipv6'] ?? null,
            $records,
        ))));
    }
}
