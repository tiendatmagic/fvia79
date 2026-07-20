<?php

namespace App\Application\Services;

use App\Domain\Entities\Coupon;
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

    public function getCouponsPaginated(int $page, int $limit, ?string $search = null): array
    {
        return $this->couponRepository->findAllPaginated($page, $limit, $search);
    }

    public function createCoupon(array $data): Coupon
    {
        if ($this->couponRepository->findByCode($data['code'])) {
            throw new \Exception("Mã giảm giá '{$data['code']}' đã tồn tại.");
        }

        $startDate = !empty($data['start_date']) ? new \DateTime($data['start_date']) : new \DateTime();
        $endDate = !empty($data['end_date']) ? new \DateTime($data['end_date']) : (new \DateTime())->modify('+7 days');

        if ($endDate <= $startDate) {
            throw new \Exception("Ngày kết thúc phải sau ngày bắt đầu.");
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
            throw new \Exception("Mã giảm giá không tồn tại.");
        }

        $existing = $this->couponRepository->findByCode($data['code']);
        if ($existing && $existing->getId() !== $id) {
            throw new \Exception("Mã giảm giá '{$data['code']}' đã được sử dụng bởi một coupon khác.");
        }

        $startDate = !empty($data['start_date']) ? new \DateTime($data['start_date']) : $coupon->getStartDate();
        $endDate = !empty($data['end_date']) ? new \DateTime($data['end_date']) : $coupon->getEndDate();

        if ($endDate <= $startDate) {
            throw new \Exception("Ngày kết thúc phải sau ngày bắt đầu.");
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
            throw new \Exception("Mã giảm giá không hợp lệ hoặc không tồn tại.");
        }

        $now = new \DateTime();
        $start = (clone $coupon->getStartDate())->setTime(0, 0, 0);
        $end = (clone $coupon->getEndDate())->setTime(23, 59, 59);

        if ($now < $start) {
            throw new \Exception("Mã giảm giá này chưa đến thời gian áp dụng.");
        }
        if ($now > $end) {
            throw new \Exception("Mã giảm giá này đã hết hạn sử dụng.");
        }

        if ($coupon->getUsageLimit() > 0 && $coupon->getUsedCount() >= $coupon->getUsageLimit()) {
            throw new \Exception("Mã giảm giá này đã hết lượt sử dụng.");
        }

        if ($cartTotal < $coupon->getMinSpend()) {
            throw new \Exception("Giá trị giỏ hàng chưa đạt mức tối thiểu " . number_format($coupon->getMinSpend()) . " VND.");
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
