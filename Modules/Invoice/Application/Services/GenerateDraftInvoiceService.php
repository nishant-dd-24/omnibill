<?php

declare(strict_types=1);

namespace Modules\Invoice\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Customer\Domain\Models\Customer;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Subscription\Domain\Models\Subscription;

class GenerateDraftInvoiceService
{
    /**
     * @param  array<int, array{description: string, quantity: int, unit_amount: int, tax_amount?: int, period_start?: \DateTimeInterface|string|null, period_end?: \DateTimeInterface|string|null}>  $lineItemsData
     */
    public function execute(
        string $tenantId,
        string $customerId,
        ?string $subscriptionId,
        array $lineItemsData,
        string $currency
    ): Invoice {
        $customer = Customer::where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        if ($subscriptionId) {
            Subscription::where('id', $subscriptionId)
                ->where('tenant_id', $tenantId)
                ->firstOrFail();
        }

        return DB::transaction(function () use ($tenantId, $customer, $subscriptionId, $lineItemsData, $currency) {
            /** @var Invoice $invoice */
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'subscription_id' => $subscriptionId,
                'number' => 'INV-'.strtoupper(Str::random(8)),
                'status' => 'draft',
                'currency' => $currency,
                'subtotal' => 0,
                'tax_total' => 0,
                'total' => 0,
                'amount_due' => 0,
                'amount_paid' => 0,
            ]);

            $subtotal = 0;
            $taxTotal = 0;

            foreach ($lineItemsData as $itemData) {
                $itemSubtotal = $itemData['quantity'] * $itemData['unit_amount'];
                $itemTax = $itemData['tax_amount'] ?? 0;
                $itemTotal = $itemSubtotal + $itemTax;

                $invoice->lineItems()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_amount' => $itemData['unit_amount'],
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTax,
                    'total' => $itemTotal,
                    'currency' => $currency,
                    'period_start' => $itemData['period_start'] ?? null,
                    'period_end' => $itemData['period_end'] ?? null,
                ]);

                $subtotal += $itemSubtotal;
                $taxTotal += $itemTax;
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $subtotal + $taxTotal,
                'amount_due' => $subtotal + $taxTotal,
            ]);

            return $invoice;
        });
    }
}
