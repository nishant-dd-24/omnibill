<?php

declare(strict_types=1);

namespace Modules\Tenant\Http\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Shared\Domain\Contracts\OmniBillUser;
use Modules\Tenant\Domain\Models\Tenant;

class TenantPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function view(Authenticatable $user, Tenant $tenant): bool
    {
        return $this->isSuperAdmin($user) || $this->isTenantMember($user, $tenant);
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, Tenant $tenant): bool
    {
        return $this->isSuperAdmin($user) || $this->hasTenantRole($user, $tenant, 'TENANT_ADMIN');
    }

    public function delete(Authenticatable $user, Tenant $tenant): bool
    {
        return $this->isSuperAdmin($user) || $this->hasTenantRole($user, $tenant, 'TENANT_ADMIN');
    }

    private function isSuperAdmin(Authenticatable $user): bool
    {
        return $user instanceof OmniBillUser && $user->hasRole('SUPER_ADMIN');
    }

    private function isTenantMember(Authenticatable $user, Tenant $tenant): bool
    {
        return $user instanceof OmniBillUser && $user->getTenantId() === (string) $tenant->id;
    }

    private function hasTenantRole(Authenticatable $user, Tenant $tenant, string $role): bool
    {
        return $this->isTenantMember($user, $tenant) && $user instanceof OmniBillUser && $user->hasRole($role);
    }
}
