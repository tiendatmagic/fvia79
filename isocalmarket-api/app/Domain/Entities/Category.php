<?php

namespace App\Domain\Entities;

class Category
{
    private ?string $id;
    private string $name;
    private string $slug;
    private ?string $description;
    private bool $isActive;
    private ?\DateTimeInterface $createdAt;
    private ?int $productsCount;

    public function __construct(
        ?string $id,
        string $name,
        string $slug,
        ?string $description = null,
        bool $isActive = true,
        ?\DateTimeInterface $createdAt = null,
        ?int $productsCount = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->productsCount = $productsCount;
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

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getProductsCount(): ?int
    {
        return $this->productsCount;
    }
}
