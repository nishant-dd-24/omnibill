<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Customer\Domain\Models\Customer;
use Modules\Customer\Domain\Models\PaymentMethod;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Str::uuid()->toString(),
            'customer_id' => Customer::factory(),
            'stripe_payment_method_id' => 'pm_'.fake()->lexify('??????????'),
            'type' => 'card',
            'last4' => fake()->numerify('####'),
            'brand' => 'visa',
            'exp_month' => fake()->numberBetween(1, 12),
            'exp_year' => fake()->numberBetween(2025, 2030),
            'is_default' => false,
        ];
    }
}
