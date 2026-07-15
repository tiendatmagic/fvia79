<?php

namespace App\Domain\Entities;

class Order
{
    private ?string $id;
    private string $userId;
    private string $orderCode;
    private float $totalPrice;
    private string $status; // pending, paid, cancelled
    /** @var OrderItem[] */
    private array $items;
    private ?\DateTime $createdAt;

    public function __construct(
        ?string $id,
        string $userId,
        string $orderCode,
        float $totalPrice,
        string $status = 'pending',
        array $items = [],
        ?\DateTime $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->orderCode = $orderCode;
        $this->totalPrice = $totalPrice;
        $this->status = $status;
        $this->items = $items;
        $this->createdAt = $createdAt;
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

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
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
