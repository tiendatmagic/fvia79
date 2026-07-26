<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\Product;
use App\Infrastructure\Persistence\Eloquent\ProductModel;

class ProductMapper
{
    public static function toDomain(ProductModel $model, int $availableCount = 0): Product
    {
        return new Product(
            $model->id,
            $model->name,
            $model->slug,
            $model->description,
            $model->price,
            $model->status,
            $availableCount,
            $model->attributes,
            $model->category_id,
            $model->deleted_at,
            $model->supplier_id,
            $model->supplier?->code,
            $model->supplier?->name,
            $model->external_id,
            $model->supplier_price_vnd,
            $model->supplier_stock,
            $model->supplier_synced_at,
            $model->name_vi,
            $model->name_en,
            $model->description_vi,
            $model->description_en
        );
    }

    public static function toPersistence(Product $entity): ProductModel
    {
        $model = new ProductModel;
        if ($entity->getId()) {
            $model = ProductModel::withTrashed()->find($entity->getId()) ?: $model;
        }

        $model->category_id = $entity->getCategoryId();
        $model->name = $entity->getName();
        $model->name_vi = $entity->getNameVi();
        $model->name_en = $entity->getNameEn();
        $model->slug = $entity->getSlug();
        $model->description = $entity->getDescription();
        $model->description_vi = $entity->getDescriptionVi();
        $model->description_en = $entity->getDescriptionEn();
        $model->price = $entity->getPrice();
        $model->status = $entity->getStatus();
        $model->attributes = $entity->getAttributes();
        $model->supplier_id = $entity->getSupplierId();
        $model->external_id = $entity->getExternalId();
        $model->supplier_price_vnd = $entity->getSupplierPriceVnd();
        $model->supplier_stock = $entity->getSupplierStock();
        $model->supplier_synced_at = $entity->getSupplierSyncedAt();

        return $model;
    }
}
