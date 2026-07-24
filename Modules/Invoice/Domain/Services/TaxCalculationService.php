<?php

declare(strict_types=1);

namespace Modules\Invoice\Domain\Services;

class TaxCalculationService
{
    /**
     * Calculates tax amount based on subtotal and rate percentage.
     *
     * @param  int  $amount  In smallest currency unit (e.g. cents)
     * @param  float  $taxRate  Percentage (e.g. 10.0 for 10%)
     * @return int Computed tax in smallest currency unit
     */
    public function calculateTax(int $amount, float $taxRate): int
    {
        return (int) round($amount * ($taxRate / 100), 0, PHP_ROUND_HALF_EVEN);
    }
}
