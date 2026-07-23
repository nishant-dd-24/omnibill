<?php

declare(strict_types=1);

namespace Modules\Customer\Http\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Customer\Domain\Models\Customer;
use Modules\Shared\Domain\Contracts\OmniBillUser;

class CustomerPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        // Any user belonging to a tenant can view their tenant's customers.
        return $user instanceof OmniBillUser && $user->getTenantId() !== null;
    }

    public function view(Authenticatable $user, Customer $customer): bool
    {
        return $this->isTenantMember($user, $customer);
    }

    public function create(Authenticatable $user): bool
    {
        return $user instanceof OmniBillUser && $user->getTenantId() !== null;
    }

    public function update(Authenticatable $user, Customer $customer): bool
    {
        return $this->isTenantMember($user, $customer);
    }

    public function delete(Authenticatable $user, Customer $customer): bool
    {
        return $this->isTenantMember($user, $customer);
    }

    private function isTenantMember(Authenticatable $user, Customer $customer): bool
    {
        return $user instanceof OmniBillUser && $user->getTenantId() === (string) $customer->tenant_id;
    }
}
