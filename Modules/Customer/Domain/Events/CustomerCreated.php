<?php

declare(strict_types=1);

namespace Modules\Customer\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Customer\Domain\Models\Customer;

class CustomerCreated
{
    use Dispatchable;

    public function __construct(public readonly Customer $customer) {}
}
