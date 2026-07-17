<?php

namespace App\Domain\Entities;

class Category
{
    private ?string $id;
    private string $name;
    private string $slug;
    private ?string $description;
    private ?\DateTimeInterface $createdAt;

    public function __construct(
        ?string $id,
        string $name,
        string $slug,
        ?string $description = null,
        ?\DateTimeInterface $createdAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->createdAt = $createdAt;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
