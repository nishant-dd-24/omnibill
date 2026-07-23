<?php

use Carbon\Carbon;
use Modules\Shared\Domain\ValueObjects\Money;
use Modules\Subscription\Domain\Services\ProrationService;

it('calculates exact half proration', function () {
    $service = new ProrationService;
    $amount = Money::fromInt(1000);
    $start = Carbon::parse('2024-01-01 00:00:00');
    $end = Carbon::parse('2024-01-31 00:00:00');
    // middle of the period
    $change = Carbon::parse('2024-01-16 00:00:00');

    $prorated = $service->calculateProration($amount, $start, $end, $change);

    expect($prorated->amount)->toBe(500);
});

it('returns full amount if change date is before or at start', function () {
    $service = new ProrationService;
    $amount = Money::fromInt(1000);
    $start = Carbon::parse('2024-01-01 00:00:00');
    $end = Carbon::parse('2024-01-31 00:00:00');
    $change = Carbon::parse('2023-12-31 00:00:00');

    $prorated = $service->calculateProration($amount, $start, $end, $change);

    expect($prorated->amount)->toBe(1000);
});

it('returns zero if change date is at or after end', function () {
    $service = new ProrationService;
    $amount = Money::fromInt(1000);
    $start = Carbon::parse('2024-01-01 00:00:00');
    $end = Carbon::parse('2024-01-31 00:00:00');
    $change = Carbon::parse('2024-02-01 00:00:00');

    $prorated = $service->calculateProration($amount, $start, $end, $change);

    expect($prorated->amount)->toBe(0);
});
