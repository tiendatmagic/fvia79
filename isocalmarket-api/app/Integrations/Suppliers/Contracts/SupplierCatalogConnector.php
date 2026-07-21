<?php

namespace App\Integrations\Suppliers\Contracts;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;

interface SupplierCatalogConnector
{
    public function fetchCatalog(SupplierModel $supplier): array;
}
