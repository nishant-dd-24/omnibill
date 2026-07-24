<?php

declare(strict_types=1);

namespace Modules\Payment\Application\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Customer\Domain\Models\Customer;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Domain\Models\PaymentAttempt;
use Modules\Subscription\Application\Adapters\BillingGatewayInterface;

class InitiatePaymentService
{
    public function __construct(private readonly BillingGatewayInterface $gateway) {}

    public function execute(string $invoiceId): string
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::findOrFail($invoiceId);

        if (strtolower($invoice->status) !== 'open') {
            throw new InvalidArgumentException('Invoice is not in open state.');
        }

        /** @var Customer $customer */
        $customer = Customer::findOrFail($invoice->customer_id);

        if (empty($customer->stripe_id)) {
            $stripeId = $this->gateway->createCustomer(
                $customer->email,
                $customer->name,
                ['tenant_id' => $customer->tenant_id]
            );
            $customer->stripe_id = $stripeId;
            $customer->save();
        }

        /** @var string $stripeIdValue */
        $stripeIdValue = $customer->stripe_id;

        $clientSecret = $this->gateway->createPaymentIntent(
            $invoice->amount_due,
            $invoice->currency,
            $stripeIdValue,
            [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
            ]
        );

        $intentParts = explode('_secret_', $clientSecret);
        $intentId = $intentParts[0];

        DB::transaction(function () use ($invoice, $customer, $intentId) {
            $payment = Payment::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'amount' => $invoice->amount_due,
                'currency' => $invoice->currency,
                'stripe_payment_intent_id' => $intentId,
                'status' => 'pending',
            ]);

            PaymentAttempt::create([
                'tenant_id' => $invoice->tenant_id,
                'payment_id' => $payment->id,
                'attempt_number' => 1,
                'status' => 'pending',
                'attempted_at' => now(),
            ]);
        });

        return $clientSecret;
    }
}
