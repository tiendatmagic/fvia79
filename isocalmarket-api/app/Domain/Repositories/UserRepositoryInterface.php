<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByEmailOrUsername(string $login): ?User;
    public function save(User $user): User;
    public function findByApiKey(string $apiKey): ?User;
    public function findAllPaginated(int $page, int $limit, ?string $search = null): array;
    public function delete(string $id): bool;
}
