<?php

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $categories = CategoryModel::orderBy('name', 'asc')->get();
        $index = 0;
        foreach ($categories as $cat) {
            $cat->sort_order = $index++;
            $cat->save();
        }
    }

    public function down(): void
    {
    }
};
