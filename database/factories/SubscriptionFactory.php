<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Subscription\Domain\Models\Subscription;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Str::uuid()->toString(),
            'customer_id' => Str::uuid()->toString(),
            'plan_id' => Str::uuid()->toString(),
            'status' => 'active',
            'stripe_id' => 'sub_'.fake()->lexify('??????????'),
        ];
    }
}
