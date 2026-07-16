<?php

namespace App\Domain\Entities;

class Category
{
    private ?string $id;
    private string $name;
    private string $slug;
    private ?string $description;

    public function __construct(
        ?string $id,
        string $name,
        string $slug,
        ?string $description = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
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
}
