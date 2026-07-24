<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_vi')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_vi');
            $table->text('description_vi')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_vi');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name_vi')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_vi');
            $table->text('description_vi')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_vi');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_vi', 'name_en', 'description_vi', 'description_en']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_vi', 'name_en', 'description_vi', 'description_en']);
        });
    }
};
