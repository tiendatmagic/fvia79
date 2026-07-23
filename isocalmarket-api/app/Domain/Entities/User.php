<?php

namespace App\Domain\Entities;

class User
{
    private ?string $id;

    private ?string $uid;

    private string $username;

    private string $name;

    private string $email;

    private string $password;

    private string $role;

    private float $balance;

    private ?string $refreshTokenHash;

    private ?\DateTime $refreshTokenExpiredAt;

    private ?string $phone;

    private ?string $google2faSecret;

    private bool $google2faEnabled;

    private ?string $apiKey;

    private string $status;

    public function __construct(
        ?string $id,
        string $username,
        string $name,
        string $email,
        string $password,
        string $role = 'customer',
        float $balance = 0.0,
        ?string $refreshTokenHash = null,
        ?\DateTime $refreshTokenExpiredAt = null,
        ?string $phone = null,
        ?string $google2faSecret = null,
        bool $google2faEnabled = false,
        ?string $apiKey = null,
        string $status = 'active',
        ?string $uid = null
    ) {
        $this->id = $id;
        $this->uid = $uid;
        $this->username = $username;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->balance = $balance;
        $this->refreshTokenHash = $refreshTokenHash;
        $this->refreshTokenExpiredAt = $refreshTokenExpiredAt;
        $this->phone = $phone;
        $this->google2faSecret = $google2faSecret;
        $this->google2faEnabled = $google2faEnabled;
        $this->apiKey = $apiKey;
        $this->status = $status;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
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

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = round($balance, 8);
    }

    public function addBalance(float $amount): void
    {
        $this->balance = round($this->balance + $amount, 8);
    }

    public function deductBalance(float $amount): void
    {
        $this->balance = round($this->balance - $amount, 8);
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }
}
