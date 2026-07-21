<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('restrict');
            $table->string('code')->index();
            $table->decimal('amount_usdt', 16, 4);
            $table->decimal('amount_to_pay', 16, 4);
            $table->decimal('amount_vnd', 16, 4);
            $table->string('tx_hash')->nullable()->unique();
            $table->enum('status', ['pending_payment', 'completed', 'failed', 'expired'])->default('pending_payment');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
