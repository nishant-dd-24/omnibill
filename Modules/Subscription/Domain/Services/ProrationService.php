<?php

declare(strict_types=1);

namespace Modules\Subscription\Domain\Services;

use DateTimeInterface;
use Modules\Shared\Domain\ValueObjects\Money;

class ProrationService
{
    /**
     * Calculate prorated amount for upgrading/downgrading.
     * Proration is calculated down to the second.
     */
    public function calculateProration(
        Money $priceAmount,
        DateTimeInterface $periodStart,
        DateTimeInterface $periodEnd,
        DateTimeInterface $changeDate
    ): Money {
        $totalSeconds = $periodEnd->getTimestamp() - $periodStart->getTimestamp();

        if ($totalSeconds <= 0) {
            return $priceAmount; // Invalid period, no proration
        }

        $remainingSeconds = max(0, $periodEnd->getTimestamp() - $changeDate->getTimestamp());
        $ratio = min(1.0, $remainingSeconds / $totalSeconds);

        return $priceAmount->multiply($ratio);
    }
}
