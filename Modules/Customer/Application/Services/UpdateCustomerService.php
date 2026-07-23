<?php

declare(strict_types=1);

namespace Modules\Customer\Application\Services;

use Modules\Customer\Domain\Models\Customer;

class UpdateCustomerService
{
    /**
     * @param  array{name?: string, email?: string, stripe_id?: string|null}  $data
     */
    public function execute(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh() ?? $customer;
    }
}
