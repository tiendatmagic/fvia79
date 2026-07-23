<?php

namespace App\Integrations\Suppliers;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use App\Integrations\Suppliers\Contracts\SupplierCatalogConnector;
use App\Integrations\Suppliers\Contracts\SupplierOrderConnector;

class SupplierConnectorFactory
{
    public function make(SupplierModel $supplier): SupplierCatalogConnector
    {
        return match ($supplier->catalog_driver) {
            CloneStoreConnector::V1, CloneStoreConnector::V2 => app(CloneStoreConnector::class),
            default => throw new \InvalidArgumentException("Unsupported supplier driver: {$supplier->catalog_driver}"),
        };
    }

    public function makeOrderConnector(SupplierModel $supplier): SupplierOrderConnector
    {
        return match ($supplier->catalog_driver) {
            CloneStoreConnector::V1, CloneStoreConnector::V2 => app(CloneStoreConnector::class),
            default => throw new \InvalidArgumentException("Unsupported supplier order driver: {$supplier->catalog_driver}"),
        };
    }
}
