<?php

namespace Tests\Unit;

use App\Integrations\Suppliers\SupplierUrlGuard;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SupplierUrlGuardTest extends TestCase
{
    #[DataProvider('unsafeUrls')]
    public function test_it_rejects_unsafe_supplier_urls(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SupplierUrlGuard)->validatedConnection($url);
    }

    public static function unsafeUrls(): array
    {
        return [
            'plain HTTP' => ['http://example.com'],
            'credentials' => ['https://user@example.com'],
            'path' => ['https://example.com/api'],
            'query' => ['https://example.com?target=internal'],
            'loopback IPv4' => ['https://127.0.0.1'],
            'link-local IPv4' => ['https://169.254.169.254'],
            'loopback IPv6' => ['https://[::1]'],
        ];
    }

    public function test_it_returns_a_public_address_for_connection_pinning(): void
    {
        $connection = (new SupplierUrlGuard)->validatedConnection('https://8.8.8.8');

        $this->assertSame('https://8.8.8.8', $connection['url']);
        $this->assertSame('8.8.8.8', $connection['host']);
        $this->assertSame(['8.8.8.8'], $connection['addresses']);
    }
}
