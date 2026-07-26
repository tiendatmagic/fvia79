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
    private string $paymentMethod;
    private ?string $payerBinanceId;
    private ?string $adminBinanceId;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;
    private ?string $userEmail;
    private ?string $userName;
    private ?string $userUid;

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
        string $paymentMethod = 'bep20',
        ?string $payerBinanceId = null,
        ?string $adminBinanceId = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null,
        ?string $userEmail = null,
        ?string $userName = null,
        ?string $userUid = null
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
        $this->paymentMethod = $paymentMethod;
        $this->payerBinanceId = $payerBinanceId;
        $this->adminBinanceId = $adminBinanceId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->userEmail = $userEmail;
        $this->userName = $userName;
        $this->userUid = $userUid;
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

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPayerBinanceId(): ?string
    {
        return $this->payerBinanceId;
    }

    public function setPayerBinanceId(?string $payerBinanceId): void
    {
        $this->payerBinanceId = $payerBinanceId;
    }

    public function getAdminBinanceId(): ?string
    {
        return $this->adminBinanceId;
    }

    public function setAdminBinanceId(?string $adminBinanceId): void
    {
        $this->adminBinanceId = $adminBinanceId;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function getUserUid(): ?string
    {
        return $this->userUid;
    }
}
