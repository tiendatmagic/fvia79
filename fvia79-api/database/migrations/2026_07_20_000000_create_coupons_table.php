<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('type')->default('percentage');
            $table->decimal('value', 15, 2)->default(10.00);
            $table->decimal('min_spend', 15, 2)->default(0.00);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('usage_limit')->default(1);
            $table->integer('used_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
