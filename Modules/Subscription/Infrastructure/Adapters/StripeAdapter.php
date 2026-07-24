<?php

declare(strict_types=1);

namespace Modules\Subscription\Infrastructure\Adapters;

use Modules\Subscription\Application\Adapters\BillingGatewayInterface;
use RuntimeException;
use Stripe\StripeClient;

class StripeAdapter implements BillingGatewayInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $secret = config('services.stripe.secret');
        if (! is_string($secret) || empty($secret)) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $this->stripe = new StripeClient($secret);
    }

    /**
     * Create a customer in the external billing gateway.
     *
     * @param  array<string, string>  $metadata
     */
    public function createCustomer(string $email, string $name, array $metadata = []): string
    {
        $customer = $this->stripe->customers->create([
            'email' => $email,
            'name' => $name,
            'metadata' => $metadata,
        ]);

        return $customer->id;
    }

    /**
     * Create a payment intent for an invoice.
     *
     * @param  int|float  $amount  Total amount in smallest currency unit (e.g. cents)
     * @param  string  $currency  e.g. 'usd'
     * @param  string  $customerId  The gateway customer ID
     * @param  array<string, string>  $metadata  Additional metadata (like invoice_id)
     * @return string The client secret of the payment intent
     */
    public function createPaymentIntent($amount, string $currency, string $customerId, array $metadata = []): string
    {
        $intent = $this->stripe->paymentIntents->create([
            'amount' => (int) $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'metadata' => $metadata,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        if (empty($intent->client_secret)) {
            throw new RuntimeException('Failed to retrieve client secret from Stripe PaymentIntent.');
        }

        return $intent->client_secret;
    }
}
