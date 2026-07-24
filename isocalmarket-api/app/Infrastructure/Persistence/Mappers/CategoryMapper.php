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
            (bool) ($model->is_active ?? true),
            $model->created_at,
            $model->products_count ?? null,
            $model->name_vi,
            $model->name_en,
            $model->description_vi,
            $model->description_en
        );
    }

    public static function toPersistence(Category $entity): CategoryModel
    {
        $model = new CategoryModel;
        if ($entity->getId()) {
            $model = CategoryModel::find($entity->getId()) ?: $model;
        }

        $model->name = $entity->getName();
        $model->name_vi = $entity->getNameVi();
        $model->name_en = $entity->getNameEn();
        $model->slug = $entity->getSlug();
        $model->description = $entity->getDescription();
        $model->description_vi = $entity->getDescriptionVi();
        $model->description_en = $entity->getDescriptionEn();
        $model->is_active = $entity->getIsActive();

        return $model;
    }
}
