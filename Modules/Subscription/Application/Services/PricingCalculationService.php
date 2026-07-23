<?php

declare(strict_types=1);

namespace Modules\Subscription\Application\Services;

use Modules\Subscription\Domain\Models\Price;

class PricingCalculationService
{
    /**
     * Calculates the price amount to be billed.
     * This ensures amount integrity server-side (BR-005).
     */
    public function calculateAmount(Price $price): int
    {
        // For v1, the amount is simply the price amount.
        // In the future, this can handle discounts, proration, taxes, etc.
        return $price->amount;
    }
}
