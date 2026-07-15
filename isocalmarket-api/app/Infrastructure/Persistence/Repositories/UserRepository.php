<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::where('email', $email)->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByEmailOrUsername(string $login): ?User
    {
        $model = UserModel::where('email', $login)
            ->orWhere('name', $login)
            ->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function save(User $user): User
    {
        $model = UserMapper::toPersistence($user);
        $model->save();
        return UserMapper::toDomain($model);
    }
}
