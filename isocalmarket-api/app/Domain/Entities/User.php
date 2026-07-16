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
    private ?string $phone;
    private ?string $google2faSecret;
    private bool $google2faEnabled;

    public function __construct(
        ?string $id,
        string $name,
        string $email,
        string $password,
        string $role = 'customer',
        ?string $refreshTokenHash = null,
        ?\DateTime $refreshTokenExpiredAt = null,
        ?string $phone = null,
        ?string $google2faSecret = null,
        bool $google2faEnabled = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->refreshTokenHash = $refreshTokenHash;
        $this->refreshTokenExpiredAt = $refreshTokenExpiredAt;
        $this->phone = $phone;
        $this->google2faSecret = $google2faSecret;
        $this->google2faEnabled = $google2faEnabled;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getGoogle2faSecret(): ?string
    {
        return $this->google2faSecret;
    }

    public function setGoogle2faSecret(?string $secret): void
    {
        $this->google2faSecret = $secret;
    }

    public function isGoogle2faEnabled(): bool
    {
        return $this->google2faEnabled;
    }

    public function setGoogle2faEnabled(bool $enabled): void
    {
        $this->google2faEnabled = $enabled;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
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
