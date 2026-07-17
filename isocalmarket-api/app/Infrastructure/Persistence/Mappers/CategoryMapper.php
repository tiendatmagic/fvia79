<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\Category;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;

class CategoryMapper
{
    public static function toDomain(CategoryModel $model): Category
    {
        return new Category(
            $model->id,
            $model->name,
            $model->slug,
            $model->description,
            $model->created_at
        );
    }

    public static function toPersistence(Category $entity): CategoryModel
    {
        $model = new CategoryModel();
        if ($entity->getId()) {
            $model = CategoryModel::find($entity->getId()) ?: $model;
        }

        $model->name = $entity->getName();
        $model->slug = $entity->getSlug();
        $model->description = $entity->getDescription();

        return $model;
    }
}
