<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\User;
use App\Infrastructure\Persistence\Eloquent\UserModel;

class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return new User(
            $model->id,
            $model->name,
            $model->email,
            $model->password,
            $model->role,
            $model->refresh_token_hash,
            $model->refresh_token_expired_at ? new \DateTime($model->refresh_token_expired_at->toDateTimeString()) : null
        );
    }

    public static function toPersistence(User $entity): UserModel
    {
        $model = new UserModel();
        if ($entity->getId()) {
            $model = UserModel::find($entity->getId()) ?: $model;
        }

        $model->name = $entity->getName();
        $model->email = $entity->getEmail();
        $model->password = $entity->getPassword();
        $model->role = $entity->getRole();
        $model->refresh_token_hash = $entity->getRefreshTokenHash();
        $model->refresh_token_expired_at = $entity->getRefreshTokenExpiredAt();

        return $model;
    }
}
