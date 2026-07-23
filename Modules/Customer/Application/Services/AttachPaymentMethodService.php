<?php

declare(strict_types=1);

namespace Modules\Customer\Application\Services;

use Modules\Customer\Domain\Events\CustomerPaymentMethodAttached;
use Modules\Customer\Domain\Models\Customer;
use Modules\Customer\Domain\Models\PaymentMethod;

class AttachPaymentMethodService
{
    /**
     * @param  array{stripe_payment_method_id: string, type?: string, last4?: string|null, brand?: string|null, exp_month?: int|null, exp_year?: int|null, is_default?: bool}  $data
     */
    public function execute(Customer $customer, array $data): PaymentMethod
    {
        $isDefault = $data['is_default'] ?? false;

        if ($isDefault) {
            // Unset current default if any
            $customer->paymentMethods()->update(['is_default' => false]);
        }

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $customer->paymentMethods()->create([
            'stripe_payment_method_id' => $data['stripe_payment_method_id'],
            'type' => $data['type'] ?? 'card',
            'last4' => $data['last4'] ?? null,
            'brand' => $data['brand'] ?? null,
            'exp_month' => $data['exp_month'] ?? null,
            'exp_year' => $data['exp_year'] ?? null,
            'is_default' => $isDefault,
        ]);

        CustomerPaymentMethodAttached::dispatch($customer, $paymentMethod);

        return $paymentMethod;
    }
}
