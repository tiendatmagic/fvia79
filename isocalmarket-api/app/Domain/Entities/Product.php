<?php

namespace App\Domain\Entities;

class Product
{
    private ?string $id;
    private string $name;
    private string $slug;
    private ?string $description;
    private float $price;
    private string $status;
    private int $availableCount;

    public function __construct(
        ?string $id,
        string $name,
        string $slug,
        ?string $description,
        float $price,
        string $status = 'active',
        int $availableCount = 0
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->price = $price;
        $this->status = $status;
        $this->availableCount = $availableCount;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAvailableCount(): int
    {
        return $this->availableCount;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
