<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Invoice\Domain\Models\Invoice;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(100, 10000);
        $taxTotal = (int) ($subtotal * 0.1);

        return [
            'id' => Str::uuid()->toString(),
            'tenant_id' => Str::uuid()->toString(),
            'customer_id' => Str::uuid()->toString(),
            'subscription_id' => Str::uuid()->toString(),
            'number' => 'INV-'.strtoupper(Str::random(8)),
            'status' => 'draft',
            'currency' => 'USD',
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $subtotal + $taxTotal,
            'amount_due' => $subtotal + $taxTotal,
            'amount_paid' => 0,
        ];
    }
}
