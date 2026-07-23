<?php

namespace App\Domain\Entities;

class OrderItem
{
    private ?string $id;
    private ?string $orderId;
    private string $productId;
    private string $productName;
    private float $price;
    private ?float $costPrice;
    private int $quantity;
    private ?array $deliveredKeys;

    public function __construct(
        ?string $id,
        ?string $orderId,
        string $productId,
        string $productName,
        float $price,
        int $quantity,
        ?array $deliveredKeys = null,
        ?float $costPrice = 0.0
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->price = $price;
        $this->costPrice = $costPrice ?? 0.0;
        $this->quantity = $quantity;
        $this->deliveredKeys = $deliveredKeys;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCostPrice(): float
    {
        return $this->costPrice ?? 0.0;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getDeliveredKeys(): ?array
    {
        return $this->deliveredKeys;
    }

    public function setDeliveredKeys(?array $deliveredKeys): void
    {
        $this->deliveredKeys = $deliveredKeys;
    }
}
