<?php

namespace Modules\IdentityAccess\Application\Services;

use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\IdentityAccess\Domain\Models\UserRole;

class AssignRole
{
    public function execute(User $user, Role $role): UserRole
    {
        /** @var UserRole $userRole */
        $userRole = $user->roles()->firstOrCreate([
            'role' => $role->value,
        ]);

        return $userRole;
    }
}
