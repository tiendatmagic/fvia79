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
            ClonetutConnector::DRIVER => app(ClonetutConnector::class),
            MediaHubConnector::DRIVER => app(MediaHubConnector::class),
            default => throw new \InvalidArgumentException("Unsupported supplier driver: {$supplier->catalog_driver}"),
        };
    }

    public function makeOrderConnector(SupplierModel $supplier): SupplierOrderConnector
    {
        return match ($supplier->catalog_driver) {
            CloneStoreConnector::V1, CloneStoreConnector::V2 => app(CloneStoreConnector::class),
            Muabm365Connector::DRIVER => app(Muabm365Connector::class),
            ClonetutConnector::DRIVER => app(ClonetutConnector::class),
            MediaHubConnector::DRIVER => app(MediaHubConnector::class),
            default => throw new \InvalidArgumentException("Unsupported supplier order driver: {$supplier->catalog_driver}"),
        };
    }

    public function detectDriver(SupplierModel $supplier): string
    {
        if (in_array($supplier->catalog_driver, [MediaHubConnector::DRIVER, ClonetutConnector::DRIVER, Muabm365Connector::DRIVER, CloneStoreConnector::V1, CloneStoreConnector::V2], true)) {
            return $supplier->catalog_driver;
        }

        try {
            return app(MediaHubConnector::class)->detectDriver($supplier);
        } catch (\Throwable) {
            try {
                return app(ClonetutConnector::class)->detectDriver($supplier);
            } catch (\Throwable) {
                try {
                    return app(Muabm365Connector::class)->detectDriver($supplier);
                } catch (\Throwable) {
                    return app(CloneStoreConnector::class)->detectDriver($supplier);
                }
            }
        }
    }
}
