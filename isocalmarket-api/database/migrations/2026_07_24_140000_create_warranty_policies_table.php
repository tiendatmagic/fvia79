<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warranty_policies', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20)->default('bm'); // 'bm' or 'via'
            $table->string('type', 20)->default('accepted'); // 'accepted' or 'rejected'
            $table->text('content_vi');
            $table->text('content_en');
            $table->text('sub_content_vi')->nullable();
            $table->text('sub_content_en')->nullable();
            $table->string('tag_vi', 50)->nullable();
            $table->string('tag_en', 50)->nullable();
            $table->string('tag_color', 20)->default('blue'); // 'blue', 'emerald', 'amber', 'rose'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'type', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_policies');
    }
};
