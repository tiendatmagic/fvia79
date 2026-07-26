<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;

class UserRepository implements UserRepositoryInterface
{
    use AccentInsensitiveSearchTrait;

    public function findById(string $id): ?User
    {
        $model = UserModel::with('membershipTier')->find($id);
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByUid(string $uid): ?User
    {
        $model = UserModel::with('membershipTier')->where('uid', $uid)->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByIdForUpdate(string $id): ?User
    {
        $model = UserModel::with('membershipTier')->where('id', $id)->lockForUpdate()->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::with('membershipTier')->where('email', $email)->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByEmailOrUsername(string $login): ?User
    {
        $query = UserModel::with('membershipTier')->where('email', $login)
            ->orWhere('username', $login);

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'uid')) {
            $query->orWhere('uid', $login);
        }

        $model = $query->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function save(User $user): User
    {
        $model = UserMapper::toPersistence($user);
        $model->save();
        $model->load('membershipTier');
        return UserMapper::toDomain($model);
    }

    public function findByApiKey(string $apiKey): ?User
    {
        $model = UserModel::with('membershipTier')->where('api_key_hash', hash('sha256', $apiKey))->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findAllPaginated(int $page, int $limit, ?string $search = null, ?string $role = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);
        $query = UserModel::with('membershipTier');
        if ($search) {
            $normalized = $this->normalizeSearchKeyword($search);
            $query->where(function ($q) use ($search, $normalized) {
                $q->where('id', $search)
                  ->orWhere('uid', $search)
                  ->orWhere('username', $search)
                  ->orWhere('email', $search)
                  ->orWhere('normalized_name', 'like', "%{$normalized}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('uid', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        if ($role) {
            $query->where('role', $role);
        }
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
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
