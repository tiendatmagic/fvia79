<?php

namespace App\Integrations\Suppliers;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class SupplierOperationLock
{
    public function run(SupplierModel $supplier, callable $operation): mixed
    {
        try {
            return Cache::lock("supplier-operation:{$supplier->id}", 180)
                ->block(15, $operation);
        } catch (LockTimeoutException $exception) {
            throw new SupplierOperationInProgressException(
                'Another operation is already running for this supplier.',
                previous: $exception,
            );
        }
    }
}
