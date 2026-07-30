<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('inventory_batches')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->text('secret_data');
            $table->string('normalized_secret_data', 255)->default('');
            $table->enum('status', ['available', 'sold', 'reserved'])->default('available');
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['batch_id', 'status']);
            $table->index('normalized_secret_data');
        });

        $batches = DB::table('inventory_batches')->get();
        foreach ($batches as $batch) {
            if (empty($batch->keys_pool)) {
                continue;
            }

            $rawLines = explode("\n", $batch->keys_pool);
            $count = 0;
            foreach ($rawLines as $rawLine) {
                $line = trim($rawLine);
                if ($line === '') {
                    continue;
                }

                $normalized = Str::slug($line, '');

                DB::table('inventory_items')->insert([
                    'id' => (string) Str::uuid7(),
                    'batch_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'secret_data' => $line,
                    'normalized_secret_data' => substr($normalized, 0, 255),
                    'status' => 'available',
                    'order_id' => null,
                    'sold_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }

            DB::table('inventory_batches')->where('id', $batch->id)->update([
                'initial_count' => DB::table('inventory_items')->where('batch_id', $batch->id)->count(),
                'sold_count' => DB::table('inventory_items')->where('batch_id', $batch->id)->where('status', 'sold')->count(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
