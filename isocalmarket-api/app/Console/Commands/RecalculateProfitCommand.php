<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecalculateProfitCommand extends Command
{
    protected $signature = 'profit:recalculate';

    protected $description = 'Recalculate and resynchronize cost_price snapshots for past order items based on batch cost prices';

    public function handle(): int
    {
        $this->info('Starting recalculation of profit & cost price snapshots...');

        $updatedCount = 0;

        DB::transaction(function () use (&$updatedCount) {
            $orderItems = OrderItemModel::all();

            foreach ($orderItems as $item) {
                $soldItems = InventoryItemModel::where('order_id', $item->order_id)
                    ->where('product_id', $item->product_id)
                    ->get();

                if ($soldItems->count() > 0) {
                    $totalBatchCost = 0.0;
                    $count = 0;

                    foreach ($soldItems as $soldItem) {
                        $batch = InventoryBatchModel::find($soldItem->batch_id);
                        if ($batch && $batch->cost_price !== null) {
                            $totalBatchCost += (float) $batch->cost_price;
                            $count++;
                        }
                    }

                    if ($count > 0) {
                        $newUnitCost = $totalBatchCost / $count;
                        $item->cost_price = $newUnitCost;
                        $item->save();
                        $updatedCount++;
                    }
                } else {
                    // Fallback: Check active or completed batches for product
                    $latestBatch = InventoryBatchModel::where('product_id', $item->product_id)
                        ->whereNotNull('cost_price')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($latestBatch) {
                        $item->cost_price = (float) $latestBatch->cost_price;
                        $item->save();
                        $updatedCount++;
                    }
                }
            }
        });

        Cache::flush();

        $this->info("Recalculated cost price snapshots for {$updatedCount} order items.");
        $this->info('Dashboard stats cache cleared successfully!');

        return self::SUCCESS;
    }
}
