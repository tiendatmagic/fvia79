<?php

namespace App\Domain\Entities;

class User
{
    private ?string $id;
    private string $name;
    private string $email;
    private string $password;
    private string $role;
    private ?string $refreshTokenHash;
    private ?\DateTime $refreshTokenExpiredAt;

    public function __construct(
        ?string $id,
        string $name,
        string $email,
        string $password,
        string $role = 'customer',
        ?string $refreshTokenHash = null,
        ?\DateTime $refreshTokenExpiredAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->refreshTokenHash = $refreshTokenHash;
        $this->refreshTokenExpiredAt = $refreshTokenExpiredAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getRefreshTokenHash(): ?string
    {
        return $this->refreshTokenHash;
    }

    public function getRefreshTokenExpiredAt(): ?\DateTime
    {
        return $this->refreshTokenExpiredAt;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function setRefreshToken(?string $hash, ?\DateTime $expiredAt): void
    {
        $this->refreshTokenHash = $hash;
        $this->refreshTokenExpiredAt = $expiredAt;
    }
}
