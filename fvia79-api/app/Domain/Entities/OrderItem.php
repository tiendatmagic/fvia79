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

    private int $supplierQuantity;

    private int $localQuantity;

    private ?string $supplierOrderId;

    private ?string $supplierCode;

    public function __construct(
        ?string $id,
        ?string $orderId,
        string $productId,
        string $productName,
        float $price,
        int $quantity,
        ?array $deliveredKeys = null,
        ?float $costPrice = 0.0,
        int $supplierQuantity = 0,
        ?int $localQuantity = null,
        ?string $supplierOrderId = null,
        ?string $supplierCode = null
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->price = $price;
        $this->costPrice = $costPrice ?? 0.0;
        $this->quantity = $quantity;
        $this->deliveredKeys = $deliveredKeys;
        $this->supplierQuantity = $supplierQuantity;
        $this->localQuantity = $localQuantity ?? $quantity;
        $this->supplierOrderId = $supplierOrderId;
        $this->supplierCode = $supplierCode;
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

    public function getSupplierQuantity(): int
    {
        return $this->supplierQuantity;
    }

    public function getLocalQuantity(): int
    {
        return $this->localQuantity;
    }

    public function getSupplierOrderId(): ?string
    {
        return $this->supplierOrderId;
    }

    public function getSupplierCode(): ?string
    {
        return $this->supplierCode;
    }
}
