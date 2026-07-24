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

    private ?string $nameVi;

    private ?string $nameEn;

    private ?string $descriptionVi;

    private ?string $descriptionEn;

    public function __construct(
        ?string $id,
        string $name,
        string $slug,
        ?string $description = null,
        bool $isActive = true,
        ?\DateTimeInterface $createdAt = null,
        ?int $productsCount = null,
        ?string $nameVi = null,
        ?string $nameEn = null,
        ?string $descriptionVi = null,
        ?string $descriptionEn = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->productsCount = $productsCount;
        $this->nameVi = $nameVi;
        $this->nameEn = $nameEn;
        $this->descriptionVi = $descriptionVi;
        $this->descriptionEn = $descriptionEn;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameVi(): ?string
    {
        return $this->nameVi;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function getDescriptionVi(): ?string
    {
        return $this->descriptionVi;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function getLocalizedName(string $lang = 'en'): string
    {
        return $lang === 'vi'
            ? ($this->nameVi ?: ($this->nameEn ?: $this->name))
            : ($this->nameEn ?: ($this->nameVi ?: $this->name));
    }

    public function getLocalizedDescription(string $lang = 'en'): ?string
    {
        return $lang === 'vi'
            ? ($this->descriptionVi ?? $this->descriptionEn ?? $this->description)
            : ($this->descriptionEn ?? $this->descriptionVi ?? $this->description);
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
