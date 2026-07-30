<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('policy_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('name_vi', 100);
            $table->string('name_en', 100);
            $table->string('icon', 50)->default('package');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        DB::table('policy_categories')->insert([
            [
                'key' => 'bm',
                'name_vi' => 'Business Manager (BM)',
                'name_en' => 'Business Manager (BM)',
                'icon' => 'package',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'via',
                'name_vi' => 'Via / Clone Facebook',
                'name_en' => 'Via / Clone Facebook',
                'icon' => 'customers',
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_categories');
    }
};
