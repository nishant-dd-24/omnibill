<?php

namespace Modules\IdentityAccess\Http\Policies;

use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasRole(Role::TENANT_ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasRole(Role::TENANT_ADMIN);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->hasRole(Role::TENANT_ADMIN);
    }
}
