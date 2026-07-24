<?php

declare(strict_types=1);

namespace Modules\Customer\Application\Services;

use Modules\Customer\Domain\Events\CustomerCreated;
use Modules\Customer\Domain\Models\Customer;

class CreateCustomerService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Customer
    {
        $customer = Customer::create([

            'name' => $data['name'],
            'email' => $data['email'],
            'stripe_id' => $data['stripe_id'] ?? null,
        ]);

        CustomerCreated::dispatch($customer);

        return $customer;
    }
}
