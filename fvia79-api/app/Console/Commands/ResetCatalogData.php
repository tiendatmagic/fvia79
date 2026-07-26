<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ResetCatalogData extends Command
{
    protected $signature = 'catalog:reset {--force : Skip the confirmation prompt}';

    protected $description = 'Back up and delete orders, inventory, products, and categories';

    private const TABLES = [
        'order_items',
        'orders',
        'inventory_batches',
        'products',
        'categories',
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Delete all catalog, inventory, and order data?')) {
            return self::SUCCESS;
        }

        $backupPath = $this->createBackup();
        $deleted = $this->deleteData();
        $this->clearCaches();

        $this->info("Backup created: {$backupPath}");
        foreach ($deleted as $table => $count) {
            $this->line("{$table}: deleted {$count}");
        }

        return self::SUCCESS;
    }

    private function createBackup(): string
    {
        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/catalog-reset-'.now()->format('Ymd-His').'.json';
        $tables = [];

        foreach (self::TABLES as $table) {
            $tables[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        File::put($path, json_encode([
            'created_at' => now()->toIso8601String(),
            'database' => config('database.connections.'.config('database.default').'.database'),
            'tables' => $tables,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $path;
    }

    private function deleteData(): array
    {
        return DB::transaction(function () {
            $deleted = [];
            foreach (self::TABLES as $table) {
                $deleted[$table] = DB::table($table)->delete();
            }

            SupplierModel::query()->update([
                'auto_sync' => false,
                'last_synced_at' => null,
                'last_sync_status' => null,
                'last_sync_error' => null,
            ]);

            return $deleted;
        });
    }

    private function clearCaches(): void
    {
        Cache::forget('products_active_storefront');
        Cache::forget('categories_active_storefront');
    }
}
