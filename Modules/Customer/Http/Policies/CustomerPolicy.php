<?php

declare(strict_types=1);

namespace Modules\Customer\Http\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Customer\Domain\Models\Customer;

class CustomerPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return isset($user->tenant_id);
    }

    public function view(Authenticatable $user, Customer $customer): bool
    {
        return $this->isTenantMember($user, $customer);
    }

    public function create(Authenticatable $user): bool
    {
        return isset($user->tenant_id);
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
        return isset($user->tenant_id) && (string) $user->tenant_id === (string) $customer->tenant_id;
    }
}
