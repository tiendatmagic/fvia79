<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\SupplierModel;
use RuntimeException;

class SupplierPriceService
{
    public function __construct(private readonly DepositService $depositService) {}

    public function calculateUsd(float $priceVnd, SupplierModel $supplier): float
    {
        $adjustedVnd = $this->applyAdjustment($priceVnd, $supplier);
        $exchangeRate = $this->depositService->getUsdtToVndRate();

        if ($exchangeRate <= 0) {
            throw new RuntimeException('VND/USD exchange rate must be greater than zero.');
        }

        return round($adjustedVnd / $exchangeRate, 2);
    }

    public function applyAdjustment(float $priceVnd, SupplierModel $supplier): float
    {
        $value = (float) $supplier->price_adjustment_value;
        $adjusted = $supplier->price_adjustment_type === 'percent'
            ? $priceVnd * (1 + $value / 100)
            : $priceVnd + $value;

        return max(0, $adjusted);
    }
}
