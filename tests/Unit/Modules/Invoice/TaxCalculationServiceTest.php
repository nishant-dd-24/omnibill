<?php

declare(strict_types=1);

use Modules\Invoice\Domain\Services\TaxCalculationService;

it('calculates tax correctly', function () {
    $service = new TaxCalculationService;

    // 10% of 1000 = 100
    expect($service->calculateTax(1000, 10.0))->toBe(100);

    // 5.5% of 1000 = 55
    expect($service->calculateTax(1000, 5.5))->toBe(55);

    // 0% of 1000 = 0
    expect($service->calculateTax(1000, 0.0))->toBe(0);
});
