<?php

namespace App\Console\Commands;

use App\Application\Services\SupplierCatalogSyncService;
use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use Illuminate\Console\Command;
use Throwable;

class SyncSupplierCatalogs extends Command
{
    protected $signature = 'suppliers:sync {supplier? : Supplier code} {--due : Sync only suppliers that are due}';
    protected $description = 'Synchronize product catalogs from external suppliers';

    public function handle(SupplierCatalogSyncService $syncService): int
    {
        $code = $this->argument('supplier');

        if ($code) {
            return $this->syncOne($syncService, $code);
        }

        if ($this->option('due')) {
            return $this->showResults($syncService->syncDue());
        }

        $status = self::SUCCESS;
        foreach (SupplierModel::where('enabled', true)->pluck('code') as $supplierCode) {
            $status = max($status, $this->syncOne($syncService, $supplierCode));
        }
        return $status;
    }

    private function syncOne(SupplierCatalogSyncService $syncService, string $code): int
    {
        try {
            $result = $syncService->syncByCode($code);
            $this->info("{$code}: {$result['categories']} categories, {$result['products']} products.");
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error("{$code}: {$exception->getMessage()}");
            return self::FAILURE;
        }
    }

    private function showResults(array $results): int
    {
        foreach ($results as $code => $result) {
            $this->info("{$code}: {$result['categories']} categories, {$result['products']} products.");
        }
        if ($results === []) {
            $this->info('No supplier catalog is due for synchronization.');
        }
        return self::SUCCESS;
    }
}
