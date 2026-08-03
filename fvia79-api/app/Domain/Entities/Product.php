<?php

namespace App\Domain\Entities;

class Product
{
    private ?string $id;

    private ?string $categoryId;

    private string $name;

    private string $slug;

    private ?string $description;

    private float $price;

    private string $status;

    private int $availableCount;

    private ?array $attributes;

    private ?\DateTimeInterface $deletedAt;

    private ?string $supplierId;

    private ?string $supplierCode;

    private ?string $supplierName;

    private ?string $externalId;

    private ?float $supplierPriceVnd;

    private ?int $supplierStock;

    private ?\DateTimeInterface $supplierSyncedAt;

    private ?string $nameVi;

    private ?string $nameEn;

    private ?string $descriptionVi;

    private ?string $descriptionEn;

    private bool $isManualPrice;

    private ?float $manualPrice;

    private string $manualPriceCurrency;

    public function __construct(
        ?string $id,
        string $name,
        string $slug,
        ?string $description,
        float $price,
        string $status = 'active',
        int $availableCount = 0,
        ?array $attributes = null,
        ?string $categoryId = null,
        ?\DateTimeInterface $deletedAt = null,
        ?string $supplierId = null,
        ?string $supplierCode = null,
        ?string $supplierName = null,
        ?string $externalId = null,
        ?float $supplierPriceVnd = null,
        ?int $supplierStock = null,
        ?\DateTimeInterface $supplierSyncedAt = null,
        ?string $nameVi = null,
        ?string $nameEn = null,
        ?string $descriptionVi = null,
        ?string $descriptionEn = null,
        bool $isManualPrice = false,
        ?float $manualPrice = null,
        ?string $manualPriceCurrency = 'VND'
    ) {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->nameVi = $nameVi;
        $this->nameEn = $nameEn;
        $this->slug = $slug;
        $this->description = $description;
        $this->descriptionVi = $descriptionVi;
        $this->descriptionEn = $descriptionEn;
        $this->price = $price;
        $this->status = $status;
        $this->availableCount = $availableCount;
        $this->attributes = $attributes;
        $this->deletedAt = $deletedAt;
        $this->supplierId = $supplierId;
        $this->supplierCode = $supplierCode;
        $this->supplierName = $supplierName;
        $this->externalId = $externalId;
        $this->supplierPriceVnd = $supplierPriceVnd;
        $this->supplierStock = $supplierStock;
        $this->supplierSyncedAt = $supplierSyncedAt;
        $this->isManualPrice = $isManualPrice;
        $this->manualPrice = $manualPrice;
        $this->manualPriceCurrency = strtoupper($manualPriceCurrency ?: 'VND');
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
        $lang = strtolower(trim($lang));
        if ($lang === 'vi') {
            return $this->nameVi ?: ($this->nameEn ?: ($this->name ?: ''));
        }

        return $this->nameEn ?: ($this->nameVi ?: ($this->name ?: ''));
    }

    public function getLocalizedDescription(string $lang = 'en'): ?string
    {
        $lang = strtolower(trim($lang));
        if ($lang === 'vi') {
            return $this->descriptionVi ?? ($this->descriptionEn ?? $this->description);
        }

        return $this->descriptionEn ?? ($this->descriptionVi ?? $this->description);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
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

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    public function getSupplierCode(): ?string
    {
        return $this->supplierCode;
    }

    public function getSupplierName(): ?string
    {
        return $this->supplierName;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getSupplierPriceVnd(): ?float
    {
        return $this->supplierPriceVnd;
    }

    public function getSupplierStock(): ?int
    {
        return $this->supplierStock;
    }

    public function getSupplierSyncedAt(): ?\DateTimeInterface
    {
        return $this->supplierSyncedAt;
    }

    public function getIsManualPrice(): bool
    {
        return $this->isManualPrice;
    }

    public function getManualPrice(): ?float
    {
        return $this->manualPrice;
    }

    public function getManualPriceCurrency(): string
    {
        return $this->manualPriceCurrency ?: 'VND';
    }
}
