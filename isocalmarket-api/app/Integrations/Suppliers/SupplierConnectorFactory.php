<?php

namespace App\Integrations\Suppliers;

use App\Integrations\Suppliers\Contracts\SupplierCatalogConnector;
use InvalidArgumentException;

class SupplierConnectorFactory
{
    public function make(string $code): SupplierCatalogConnector
    {
        return match ($code) {
            'ads7979' => app(Ads7979Connector::class),
            default => throw new InvalidArgumentException("Unsupported supplier: {$code}"),
        };
    }
}
