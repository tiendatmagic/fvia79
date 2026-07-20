<?php

namespace App\Domain\Entities;

class Order
{
    private ?string $id;
    private string $userId;
    private string $orderCode;
    private float $totalPrice;
    private string $status; // pending, paid, cancelled
    private array $items;
    private ?\DateTime $createdAt;
    private ?string $userEmail;
    private ?string $couponCode;
    private float $discountAmount;
    private ?string $couponId;

    public function __construct(
        ?string $id,
        string $userId,
        string $orderCode,
        float $totalPrice,
        string $status = 'pending',
        array $items = [],
        ?\DateTime $createdAt = null,
        ?string $userEmail = null,
        ?string $couponCode = null,
        float $discountAmount = 0.0,
        ?string $couponId = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->orderCode = $orderCode;
        $this->totalPrice = $totalPrice;
        $this->status = $status;
        $this->items = $items;
        $this->createdAt = $createdAt;
        $this->userEmail = $userEmail;
        $this->couponCode = $couponCode;
        $this->discountAmount = $discountAmount;
        $this->couponId = $couponId;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getOrderCode(): string
    {
        return $this->orderCode;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function getDiscountAmount(): float
    {
        return $this->discountAmount;
    }

    public function getCouponId(): ?string
    {
        return $this->couponId;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
