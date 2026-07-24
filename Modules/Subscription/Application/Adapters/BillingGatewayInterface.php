<?php

declare(strict_types=1);

namespace Modules\Subscription\Application\Adapters;

interface BillingGatewayInterface
{
    /**
     * Create a customer in the external billing gateway.
     *
     * @param  array<string, string>  $metadata
     * @return string The external gateway customer ID (e.g., stripe_id)
     */
    public function createCustomer(string $email, string $name, array $metadata = []): string;

    /**
     * Create a payment intent for an invoice.
     *
     * @param  int|float  $amount  Total amount in smallest currency unit (e.g. cents)
     * @param  string  $currency  e.g. 'usd'
     * @param  string  $customerId  The gateway customer ID
     * @param  array<string, string>  $metadata  Additional metadata (like invoice_id)
     * @return string The client secret of the payment intent
     */
    public function createPaymentIntent($amount, string $currency, string $customerId, array $metadata = []): string;
}
