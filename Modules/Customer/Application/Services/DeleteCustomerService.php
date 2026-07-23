<?php

declare(strict_types=1);

namespace Modules\Customer\Application\Services;

use Modules\Customer\Domain\Models\Customer;

class DeleteCustomerService
{
    public function execute(Customer $customer): void
    {
        $customer->delete();
    }
}
