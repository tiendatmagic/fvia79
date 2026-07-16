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
            $model->attributes
        );
    }

    public static function toPersistence(Product $entity): ProductModel
    {
        $model = new ProductModel();
        if ($entity->getId()) {
            $model = ProductModel::find($entity->getId()) ?: $model;
        }

        $model->name = $entity->getName();
        $model->slug = $entity->getSlug();
        $model->description = $entity->getDescription();
        $model->price = $entity->getPrice();
        $model->status = $entity->getStatus();
        $model->attributes = $entity->getAttributes();

        return $model;
    }
}
