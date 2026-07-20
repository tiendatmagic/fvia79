<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Coupon;

interface CouponRepositoryInterface
{
    public function save(Coupon $coupon): Coupon;
    public function findById(string $id): ?Coupon;
    public function findByCode(string $code): ?Coupon;
    public function findAll(): array;
    public function findAllPaginated(int $page, int $limit, ?string $search = null): array;
    public function delete(string $id): bool;
    public function restore(string $id): bool;
}
