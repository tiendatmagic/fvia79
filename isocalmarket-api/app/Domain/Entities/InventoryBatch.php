<?php

namespace App\Domain\Entities;

class InventoryBatch
{
    private ?string $id;
    private string $productId;
    private string $batchName;
    private ?string $keysPool;
    private ?float $costPrice;
    private int $initialCount;
    private int $soldCount;
    private string $status;
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;

    public function __construct(
        ?string $id,
        string $productId,
        string $batchName,
        ?string $keysPool,
        int $initialCount = 0,
        int $soldCount = 0,
        string $status = 'active',
        ?\DateTimeInterface $createdAt = null,
        ?\DateTimeInterface $updatedAt = null,
        ?float $costPrice = null
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->batchName = $batchName;
        $this->keysPool = $keysPool;
        $this->costPrice = $costPrice;
        $this->initialCount = $initialCount;
        $this->soldCount = $soldCount;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getBatchName(): string
    {
        return $this->batchName;
    }

    public function getKeysPool(): ?string
    {
        return $this->keysPool;
    }

    public function getCostPrice(): ?float
    {
        return $this->costPrice;
    }

    public function setCostPrice(?float $costPrice): void
    {
        $this->costPrice = $costPrice;
    }

    public function getInitialCount(): int
    {
        return $this->initialCount;
    }

    public function getSoldCount(): int
    {
        return $this->soldCount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getAvailableCount(): int
    {
        if (empty($this->keysPool)) {
            return 0;
        }
        $lines = preg_split('/\r\n|\r|\n/', trim($this->keysPool));
        $filtered = array_filter($lines, fn($line) => !empty(trim($line)));
        return count($filtered);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    public function popKeys(int $quantity): array
    {
        if ($quantity <= 0 || empty($this->keysPool)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $this->keysPool);
        $availableKeys = [];
        $remainingKeys = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }
            if (count($availableKeys) < $quantity) {
                $availableKeys[] = $trimmed;
            } else {
                $remainingKeys[] = $trimmed;
            }
        }

        $poppedCount = count($availableKeys);
        $this->soldCount += $poppedCount;
        $this->keysPool = implode("\n", $remainingKeys);

        if (count($remainingKeys) === 0) {
            $this->status = 'completed';
        }

        return $availableKeys;
    }
}
