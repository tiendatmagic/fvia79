<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ResetProfitCommand extends Command
{
    protected $signature = 'profit:reset';

    protected $description = 'Reset gross profit to 0 by setting order item cost prices equal to selling prices';

    public function handle(): int
    {
        $this->info('Resetting profit to 0 for all historical orders...');

        $updatedCount = 0;

        DB::transaction(function () use (&$updatedCount) {
            $updatedCount = OrderItemModel::query()->update([
                'cost_price' => DB::raw('price')
            ]);
        });

        // Flush all dashboard caches
        Cache::flush();

        $this->info("Successfully set cost_price = price for {$updatedCount} order items. Profit is now 0.");
        $this->info('Dashboard stats cache cleared successfully!');

        return self::SUCCESS;
    }
}
