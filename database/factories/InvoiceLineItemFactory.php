<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Invoice\Domain\Models\InvoiceLineItem;

/**
 * @extends Factory<InvoiceLineItem>
 */
class InvoiceLineItemFactory extends Factory
{
    protected $model = InvoiceLineItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = $this->faker->numberBetween(1, 10);
        $unitAmount = $this->faker->numberBetween(100, 1000);
        $subtotal = $qty * $unitAmount;
        $tax = (int) ($subtotal * 0.1);

        return [
            'id' => Str::uuid()->toString(),
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->sentence(),
            'quantity' => $qty,
            'unit_amount' => $unitAmount,
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $subtotal + $tax,
            'currency' => 'USD',
        ];
    }
}
