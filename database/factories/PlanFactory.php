<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Subscription\Domain\Models\Plan;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'stripe_product_id' => 'prod_'.Str::random(14),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'features' => ['users' => 10, 'storage' => '50GB'],
            'is_active' => true,
        ];
    }
}
