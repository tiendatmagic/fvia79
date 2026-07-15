<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByEmailOrUsername(string $login): ?User;
    public function save(User $user): User;
}
