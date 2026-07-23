<?php

namespace App\Application\Services;

use App\Domain\Entities\Coupon;
use App\Domain\Exceptions\CouponException;
use App\Domain\Repositories\CouponRepositoryInterface;

class CouponService
{
    private CouponRepositoryInterface $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    public function getCouponById(string $id): ?Coupon
    {
        return $this->couponRepository->findById($id);
    }

    public function getCouponByCode(string $code): ?Coupon
    {
        return $this->couponRepository->findByCode($code);
    }

    public function getAllCoupons(): array
    {
        return $this->couponRepository->findAll();
    }

    public function getCouponsPaginated(
        int $page,
        int $limit,
        ?string $search = null,
        ?string $status = null,
        ?string $type = null
    ): array {
        return $this->couponRepository->findAllPaginated($page, $limit, $search, $status, $type);
    }

    public function createCoupon(array $data): Coupon
    {
        if ($this->couponRepository->findByCode($data['code'])) {
            throw new CouponException("Coupon '{$data['code']}' already exists.");
        }

        $startDate = !empty($data['start_date']) ? new \DateTime($data['start_date']) : new \DateTime();
        $endDate = !empty($data['end_date']) ? new \DateTime($data['end_date']) : (new \DateTime())->modify('+7 days');

        if ($endDate <= $startDate) {
            throw new CouponException('End date must be after start date.');
        }

        $coupon = new Coupon(
            null,
            strtoupper(trim($data['code'])),
            $data['type'] ?? 'percentage',
            (float) ($data['value'] ?? 10.0),
            (float) ($data['min_spend'] ?? 0.0),
            $startDate,
            $endDate,
            (int) ($data['usage_limit'] ?? 1),
            0
        );

        return $this->couponRepository->save($coupon);
    }

    public function updateCoupon(string $id, array $data): Coupon
    {
        $coupon = $this->couponRepository->findById($id);
        if (!$coupon) {
            throw new CouponException('Coupon not found.');
        }

        $existing = $this->couponRepository->findByCode($data['code']);
        if ($existing && $existing->getId() !== $id) {
            throw new CouponException("Coupon code '{$data['code']}' is already used by another coupon.");
        }

        $startDate = !empty($data['start_date']) ? new \DateTime($data['start_date']) : $coupon->getStartDate();
        $endDate = !empty($data['end_date']) ? new \DateTime($data['end_date']) : $coupon->getEndDate();

        if ($endDate <= $startDate) {
            throw new CouponException('End date must be after start date.');
        }

        $updatedCoupon = new Coupon(
            $id,
            strtoupper(trim($data['code'])),
            $data['type'] ?? $coupon->getType(),
            (float) ($data['value'] ?? $coupon->getValue()),
            (float) ($data['min_spend'] ?? $coupon->getMinSpend()),
            $startDate,
            $endDate,
            (int) ($data['usage_limit'] ?? $coupon->getUsageLimit()),
            $coupon->getUsedCount(),
            $coupon->getCreatedAt()
        );

        return $this->couponRepository->save($updatedCoupon);
    }

    public function deleteCoupon(string $id): bool
    {
        return $this->couponRepository->delete($id);
    }

    public function restoreCoupon(string $id): bool
    {
        return $this->couponRepository->restore($id);
    }

    public function validateCoupon(string $code, float $cartTotal): array
    {
        $coupon = $this->couponRepository->findByCode(strtoupper(trim($code)));
        if (!$coupon) {
            throw new CouponException('Coupon code is invalid or does not exist.');
        }

        $now = new \DateTime();
        $start = (clone $coupon->getStartDate())->setTime(0, 0, 0);
        $end = (clone $coupon->getEndDate())->setTime(23, 59, 59);

        if ($now < $start) {
            throw new CouponException('This coupon is not active yet.');
        }
        if ($now > $end) {
            throw new CouponException('This coupon has expired.');
        }

        if ($coupon->getUsageLimit() > 0 && $coupon->getUsedCount() >= $coupon->getUsageLimit()) {
            throw new CouponException('This coupon has reached its usage limit.');
        }

        if ($cartTotal < $coupon->getMinSpend()) {
            throw new CouponException('Cart total must be at least ' . number_format($coupon->getMinSpend()) . ' USD.');
        }

        $discount = $coupon->calculateDiscount($cartTotal);

        return [
            'valid' => true,
            'coupon_id' => $coupon->getId(),
            'code' => $coupon->getCode(),
            'type' => $coupon->getType(),
            'value' => $coupon->getValue(),
            'min_spend' => $coupon->getMinSpend(),
            'discount_amount' => $discount
        ];
    }
}
