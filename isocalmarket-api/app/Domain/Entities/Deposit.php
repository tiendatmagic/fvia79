<?php

namespace App\Domain\Entities;

class Deposit
{
    private ?string $id;
    private string $userId;
    private string $code;
    private float $amountUsdt;
    private float $amountToPay;
    private float $amountVnd;
    private ?string $txHash;
    private ?string $adminWalletAddress;
    private string $status;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    public function __construct(
        ?string $id,
        string $userId,
        string $code,
        float $amountUsdt,
        float $amountToPay,
        float $amountVnd,
        ?string $txHash = null,
        ?string $adminWalletAddress = null,
        string $status = 'pending_payment',
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->code = $code;
        $this->amountUsdt = $amountUsdt;
        $this->amountToPay = $amountToPay;
        $this->amountVnd = $amountVnd;
        $this->txHash = $txHash;
        $this->adminWalletAddress = $adminWalletAddress;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAmountUsdt(): float
    {
        return $this->amountUsdt;
    }

    public function getAmountToPay(): float
    {
        return $this->amountToPay;
    }

    public function getAmountVnd(): float
    {
        return $this->amountVnd;
    }

    public function getTxHash(): ?string
    {
        return $this->txHash;
    }

    public function setTxHash(?string $txHash): void
    {
        $this->txHash = $txHash;
    }

    public function getAdminWalletAddress(): ?string
    {
        return $this->adminWalletAddress;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
