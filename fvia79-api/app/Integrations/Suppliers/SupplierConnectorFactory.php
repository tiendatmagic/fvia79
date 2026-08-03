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
            Muabm365Connector::DRIVER => app(Muabm365Connector::class),
            default => throw new \InvalidArgumentException("Unsupported supplier driver: {$supplier->catalog_driver}"),
        };
    }

    public function makeOrderConnector(SupplierModel $supplier): SupplierOrderConnector
    {
        return match ($supplier->catalog_driver) {
            CloneStoreConnector::V1, CloneStoreConnector::V2 => app(CloneStoreConnector::class),
            Muabm365Connector::DRIVER => app(Muabm365Connector::class),
            default => throw new \InvalidArgumentException("Unsupported supplier order driver: {$supplier->catalog_driver}"),
        };
    }

    public function detectDriver(SupplierModel $supplier): string
    {
        if ($supplier->catalog_driver === Muabm365Connector::DRIVER) {
            return app(Muabm365Connector::class)->detectDriver($supplier);
        }

        if (in_array($supplier->catalog_driver, [CloneStoreConnector::V1, CloneStoreConnector::V2], true)) {
            return app(CloneStoreConnector::class)->detectDriver($supplier);
        }

        try {
            return app(Muabm365Connector::class)->detectDriver($supplier);
        } catch (\Throwable) {
            return app(CloneStoreConnector::class)->detectDriver($supplier);
        }
    }
}
