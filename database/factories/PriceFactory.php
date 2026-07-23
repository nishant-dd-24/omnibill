<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Price;

/**
 * @extends Factory<Price>
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'stripe_price_id' => 'price_'.Str::random(14),
            'amount' => 1000,
            'currency' => 'USD',
            'interval' => 'month',
            'interval_count' => 1,
            'is_active' => true,
        ];
    }
}
