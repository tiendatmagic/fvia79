<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FixSupplierViNames extends Command
{
    protected $signature = 'supplier:fix-vi-names';

    protected $description = 'Fix Vietnamese product and category names that were mistakenly auto-translated by restoring raw names';

    public function handle(): int
    {
        $this->info('Fixing Category Vietnamese names...');
        $categories = CategoryModel::all();
        $catFixed = 0;

        foreach ($categories as $category) {
            $rawName = data_get($category->attributes, 'raw_name');
            $updated = false;

            if ($rawName && $category->name_vi !== $rawName) {
                $category->name_vi = $rawName;
                $category->name = $rawName;
                $updated = true;
            } elseif (str_starts_with((string) $category->name_vi, 'Qua ')) {
                $fixedName = 'Via '.substr((string) $category->name_vi, 4);
                $category->name_vi = $fixedName;
                $category->name = $fixedName;
                $updated = true;
            }

            if ($updated) {
                $category->save();
                $catFixed++;
            }
        }

        $this->info("Fixed {$catFixed} categories.");

        $this->info('Fixing Product Vietnamese names...');
        $products = ProductModel::all();
        $prodFixed = 0;

        foreach ($products as $product) {
            $rawName = data_get($product->attributes, 'raw_name');
            $rawDesc = data_get($product->attributes, 'raw_desc');
            $updated = false;

            if ($rawName && $product->name_vi !== $rawName) {
                $product->name_vi = $rawName;
                $product->name = $rawName;
                $updated = true;
            } elseif (str_starts_with((string) $product->name_vi, 'Qua ')) {
                $fixedName = 'Via '.substr((string) $product->name_vi, 4);
                $product->name_vi = $fixedName;
                $product->name = $fixedName;
                $updated = true;
            }

            if ($rawDesc !== null && $product->description_vi !== $rawDesc) {
                $product->description_vi = $rawDesc;
                $product->description = $rawDesc;
                $updated = true;
            }

            if ($updated) {
                $product->save();
                $prodFixed++;
            }
        }

        $this->info("Fixed {$prodFixed} products.");

        Cache::forget('products_active_storefront');
        Cache::forget('categories_active_storefront');
        Cache::forget('categories_active_storefront_en');
        Cache::forget('categories_active_storefront_vi');

        $this->info('Cache cleared and fix completed successfully!');

        return Command::SUCCESS;
    }
}
