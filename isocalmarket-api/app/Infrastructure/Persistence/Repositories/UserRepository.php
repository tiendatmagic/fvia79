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
            ->orWhere('username', $login)
            ->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function save(User $user): User
    {
        $model = UserMapper::toPersistence($user);
        $model->save();
        return UserMapper::toDomain($model);
    }

    public function findByApiKey(string $apiKey): ?User
    {
        $model = UserModel::where('api_key', $apiKey)->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findAllPaginated(int $page, int $limit, ?string $search = null, ?string $role = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = UserModel::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($role) {
            $query->where('role', $role);
        }
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $models = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $users = [];
        foreach ($models as $model) {
            $users[] = UserMapper::toDomain($model);
        }

        return [
            'items' => $users,
            'total' => $total
        ];
    }

    public function delete(string $id): bool
    {
        $model = UserModel::find($id);
        if ($model) {
            return (bool) $model->delete();
        }
        return false;
    }
}
