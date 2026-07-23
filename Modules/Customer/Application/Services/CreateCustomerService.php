<?php

declare(strict_types=1);

namespace Modules\Customer\Application\Services;

use Modules\Customer\Domain\Events\CustomerCreated;
use Modules\Customer\Domain\Models\Customer;
use Modules\Shared\Domain\Context\CurrentTenant;

class CreateCustomerService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Customer
    {
        $customer = Customer::create([
            'tenant_id' => app(CurrentTenant::class)->id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'stripe_id' => $data['stripe_id'] ?? null,
        ]);

        CustomerCreated::dispatch($customer);

        return $customer;
    }
}
