<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Invoice\Domain\Models\CreditNote;
use Modules\Invoice\Domain\Models\Invoice;

/**
 * @extends Factory<CreditNote>
 */
class CreditNoteFactory extends Factory
{
    protected $model = CreditNote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'tenant_id' => Str::uuid()->toString(),
            'invoice_id' => Str::uuid()->toString(), // Usually references a real invoice, but here we keep it simple
            'number' => 'CN-'.strtoupper(Str::random(8)),
            'amount' => $this->faker->numberBetween(100, 1000),
            'currency' => 'USD',
            'reason' => $this->faker->sentence(),
            'issued_at' => now(),
        ];
    }
}
