<?php

declare(strict_types=1);

namespace Modules\Customer\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Customer\Domain\Models\Customer;
use Modules\Customer\Domain\Models\PaymentMethod;

class CustomerPaymentMethodAttached
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly PaymentMethod $paymentMethod
    ) {}
}
