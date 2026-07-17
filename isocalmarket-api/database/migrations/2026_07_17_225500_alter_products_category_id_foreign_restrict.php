<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key cũ
            $table->dropForeign(['category_id']);
            
            // Định nghĩa lại foreign key với onDelete('restrict')
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }
};
