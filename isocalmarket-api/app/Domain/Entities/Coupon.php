<?php

namespace App\Domain\Entities;

class Coupon
{
    private ?string $id;
    private string $code;
    private string $type; // percentage, fixed
    private float $value;
    private float $minSpend;
    private \DateTimeInterface $startDate;
    private \DateTimeInterface $endDate;
    private int $usageLimit;
    private int $usedCount;
    private ?\DateTimeInterface $createdAt;

    public function __construct(
        ?string $id,
        string $code,
        string $type,
        float $value,
        float $minSpend,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $usageLimit = 1,
        int $usedCount = 0,
        ?\DateTimeInterface $createdAt = null
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->type = $type;
        $this->value = $value;
        $this->minSpend = $minSpend;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->usageLimit = $usageLimit;
        $this->usedCount = $usedCount;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getMinSpend(): float
    {
        return $this->minSpend;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function getUsageLimit(): int
    {
        return $this->usageLimit;
    }

    public function getUsedCount(): int
    {
        return $this->usedCount;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isValidForAmount(float $amount): bool
    {
        $now = new \DateTime();
        $start = (clone $this->startDate)->setTime(0, 0, 0);
        $end = (clone $this->endDate)->setTime(23, 59, 59);

        if ($now < $start || $now > $end) {
            return false;
        }

        if ($this->usageLimit > 0 && $this->usedCount >= $this->usageLimit) {
            return false;
        }

        if ($amount < $this->minSpend) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if (!$this->isValidForAmount($amount)) {
            return 0.0;
        }

        if ($this->type === 'percentage') {
            $discount = round($amount * ($this->value / 100.0), 2);
            return min($discount, $amount);
        }

        return min($this->value, $amount);
    }
}
