<?php

namespace App\Domain\Entities;

class InventoryItem
{
    private ?string $id;
    private string $productId;
    private string $data; // Dữ liệu tài nguyên số cụ thể (credential, raw key...)
    private string $status; // available, reserved, sold
    private ?string $orderId;

    public function __construct(
        ?string $id,
        string $productId,
        string $data,
        string $status = 'available',
        ?string $orderId = null
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->data = $data;
        $this->status = $status;
        $this->orderId = $orderId;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function markAsSold(string $orderId): void
    {
        $this->status = 'sold';
        $this->orderId = $orderId;
    }
}
