<?php

namespace App\Integrations\Suppliers\Contracts;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;

interface SupplierOrderConnector
{
    /**
     * @return array{order_id: string, resources: array<int, string>, raw: array}
     */
    public function purchase(SupplierModel $supplier, string $externalProductId, int $quantity): array;

    /**
     * @return array{order_id: string, resources: array<int, string>, raw: array}
     */
    public function fetchOrder(SupplierModel $supplier, string $supplierOrderId): array;
}
