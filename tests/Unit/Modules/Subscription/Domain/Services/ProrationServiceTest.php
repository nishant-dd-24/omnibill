<?php

use Carbon\Carbon;
use Modules\Shared\Domain\ValueObjects\Money;
use Modules\Subscription\Domain\Services\ProrationService;

it('calculates proration correctly for various edge cases', function (string $start, string $end, string $change, int $expectedAmount) {
    $service = new ProrationService;
    $amount = Money::fromInt(1000);

    $startCarbon = Carbon::parse($start);
    $endCarbon = Carbon::parse($end);
    $changeCarbon = Carbon::parse($change);

    $prorated = $service->calculateProration($amount, $startCarbon, $endCarbon, $changeCarbon);

    expect($prorated->amount)->toBe($expectedAmount);
})->with([
    'exact half' => ['2024-01-01 00:00:00', '2024-01-31 00:00:00', '2024-01-16 00:00:00', 500],
    'change before start' => ['2024-01-01 00:00:00', '2024-01-31 00:00:00', '2023-12-31 00:00:00', 1000],
    'change exactly at start' => ['2024-01-01 00:00:00', '2024-01-31 00:00:00', '2024-01-01 00:00:00', 1000],
    'change exactly at end' => ['2024-01-01 00:00:00', '2024-01-31 00:00:00', '2024-01-31 00:00:00', 0],
    'change after end' => ['2024-01-01 00:00:00', '2024-01-31 00:00:00', '2024-02-01 00:00:00', 0],

    // Leap year cases
    'leap year crossing Feb 29 exactly half' => [
        '2024-02-01 00:00:00',
        '2024-03-01 00:00:00', // 29 days total
        '2024-02-15 12:00:00', // exactly 14.5 days
        500,
    ],
    'non-leap year Feb exactly half' => [
        '2023-02-01 00:00:00',
        '2023-03-01 00:00:00', // 28 days total
        '2023-02-15 00:00:00', // exactly 14 days
        500,
    ],

    // Month-end shifts
    'Jan 31 to Feb 28 exact half' => [
        '2023-01-31 00:00:00',
        '2023-02-28 00:00:00', // 28 days total
        '2023-02-14 00:00:00', // exactly 14 days
        500,
    ],
]);
