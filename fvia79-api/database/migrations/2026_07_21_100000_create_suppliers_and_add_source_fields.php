<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('base_url');
            $table->text('api_key')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('auto_sync')->default(false);
            $table->unsignedSmallInteger('sync_interval_minutes')->default(30);
            $table->enum('price_adjustment_type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('price_adjustment_value', 15, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();
        });

        DB::table('suppliers')->insert([
            'id' => (string) Str::uuid7(),
            'code' => 'ads7979',
            'name' => 'ADS7979',
            'base_url' => 'https://ads7979.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignUuid('supplier_id')->nullable()->after('id')->constrained('suppliers')->nullOnDelete();
            $table->string('external_id')->nullable()->after('supplier_id');
            $table->unique(['supplier_id', 'external_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignUuid('supplier_id')->nullable()->after('id')->constrained('suppliers')->nullOnDelete();
            $table->string('external_id')->nullable()->after('supplier_id');
            $table->decimal('supplier_price_vnd', 15, 2)->nullable()->after('price');
            $table->unsignedInteger('supplier_stock')->nullable()->after('supplier_price_vnd');
            $table->timestamp('supplier_synced_at')->nullable()->after('supplier_stock');
            $table->unique(['supplier_id', 'external_id']);
            $table->index(['supplier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropUnique(['supplier_id', 'external_id']);
            $table->dropIndex(['supplier_id', 'status']);
            $table->dropColumn(['supplier_id', 'external_id', 'supplier_price_vnd', 'supplier_stock', 'supplier_synced_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropUnique(['supplier_id', 'external_id']);
            $table->dropColumn(['supplier_id', 'external_id']);
        });

        Schema::dropIfExists('suppliers');
    }
};
