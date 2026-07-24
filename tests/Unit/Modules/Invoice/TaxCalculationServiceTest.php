<?php

declare(strict_types=1);

use Modules\Invoice\Domain\Services\TaxCalculationService;

it('calculates tax correctly using bankers rounding', function ($amount, $rate, $expected) {
    $service = new TaxCalculationService;

    expect($service->calculateTax($amount, $rate))->toBe($expected);
})->with([
    '10% of 1000' => [1000, 10.0, 100],
    '5.5% of 1000' => [1000, 5.5, 55],
    '0% of 1000' => [1000, 0.0, 0],
    'rounds half to even (down)' => [105, 10.0, 10], // 105 * 0.10 = 10.5 -> 10
    'rounds half to even (up)' => [115, 10.0, 12],   // 115 * 0.10 = 11.5 -> 12
    'rounds down normally' => [104, 10.0, 10],       // 104 * 0.10 = 10.4 -> 10
    'rounds up normally' => [106, 10.0, 11],         // 106 * 0.10 = 10.6 -> 11
]);
