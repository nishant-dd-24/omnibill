<?php

declare(strict_types=1);

namespace Modules\Customer\Application\Services;

use Modules\Customer\Domain\Models\Customer;

class CreateCustomerService
{
    /**
     * @param  array{name: string, email: string, stripe_id?: string|null}  $data
     */
    public function execute(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'stripe_id' => $data['stripe_id'] ?? null,
        ]);
    }
}
