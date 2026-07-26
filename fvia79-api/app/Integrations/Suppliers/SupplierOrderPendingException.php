<?php

namespace App\Integrations\Suppliers;

use RuntimeException;
use Throwable;

class SupplierOrderPendingException extends RuntimeException
{
    public function __construct(
        public readonly string $supplierOrderId,
        public readonly array $responsePayload,
        string $message = 'Supplier order is not ready yet.',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }
}
