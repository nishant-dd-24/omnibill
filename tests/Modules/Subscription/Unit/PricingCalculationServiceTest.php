<?php

use Modules\Subscription\Application\Services\PricingCalculationService;
use Modules\Subscription\Domain\Models\Price;

it('calculates the amount', function () {
    $price = new Price(['amount' => 1000]);

    $service = new PricingCalculationService;

    expect($service->calculateAmount($price))->toBe(1000);
});
