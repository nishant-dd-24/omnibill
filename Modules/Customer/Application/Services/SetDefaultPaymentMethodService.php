<?php

declare(strict_types=1);

namespace Modules\Customer\Application\Services;

use Modules\Customer\Domain\Models\Customer;
use Modules\Customer\Domain\Models\PaymentMethod;

class SetDefaultPaymentMethodService
{
    public function execute(Customer $customer, PaymentMethod $paymentMethod): void
    {
        // Unset current default
        $customer->paymentMethods()->update(['is_default' => false]);

        // Set new default
        $paymentMethod->update(['is_default' => true]);
    }
}
