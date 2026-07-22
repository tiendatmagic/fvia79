<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;

$batches = InventoryBatchModel::where('status', 'active')->get();
echo "TOTAL ACTIVE INVENTORY BATCHES IN DB: " . $batches->count() . "\n";

foreach ($batches as $b) {
    $p = ProductModel::find($b->product_id);
    echo "Batch ID: " . $b->id . " | Name: " . $b->batch_name . " | Product: " . ($p ? $p->name : 'N/A') . " | Initial: " . $b->initial_count . " | Sold: " . $b->sold_count . " | Available: " . ($b->initial_count - $b->sold_count) . "\n";
}
