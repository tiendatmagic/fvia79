<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('warranty_policies', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20)->default('bm');
            $table->string('type', 20)->default('accepted');
            $table->text('content_vi');
            $table->text('content_en');
            $table->text('sub_content_vi')->nullable();
            $table->text('sub_content_en')->nullable();
            $table->string('tag_vi', 50)->nullable();
            $table->string('tag_en', 50)->nullable();
            $table->string('tag_color', 20)->default('blue');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'type', 'is_active']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_policies');
    }
};
