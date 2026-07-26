<?php

namespace App\Domain\Entities;

class InventoryItem
{
    private ?string $id;
    private string $batchId;
    private string $productId;
    private string $secretData;
    private string $status;
    private ?string $orderId;
    private ?\DateTimeInterface $soldAt;
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;

    private ?string $batchName;
    private ?string $productName;

    public function __construct(
        ?string $id,
        string $batchId,
        string $productId,
        string $secretData,
        string $status = 'available',
        ?string $orderId = null,
        ?\DateTimeInterface $soldAt = null,
        ?\DateTimeInterface $createdAt = null,
        ?\DateTimeInterface $updatedAt = null,
        ?string $batchName = null,
        ?string $productName = null
    ) {
        $this->id = $id;
        $this->batchId = $batchId;
        $this->productId = $productId;
        $this->secretData = $secretData;
        $this->status = $status;
        $this->orderId = $orderId;
        $this->soldAt = $soldAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->batchName = $batchName;
        $this->productName = $productName;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getSecretData(): string
    {
        return $this->secretData;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function getSoldAt(): ?\DateTimeInterface
    {
        return $this->soldAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getBatchName(): ?string
    {
        return $this->batchName;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setSecretData(string $secretData): void
    {
        $this->secretData = $secretData;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function markAsSold(string $orderId): void
    {
        $this->status = 'sold';
        $this->orderId = $orderId;
        $this->soldAt = new \DateTimeImmutable();
    }
}
