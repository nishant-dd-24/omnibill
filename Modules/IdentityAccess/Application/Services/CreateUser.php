<?php

namespace Modules\IdentityAccess\Application\Services;

use Illuminate\Support\Facades\Hash;
use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Shared\Domain\Context\CurrentTenant;

class CreateUser
{
    public function __construct(
        private readonly AssignRole $assignRole,
        private readonly CurrentTenant $currentTenant
    ) {}

    public function execute(string $name, string $email, string $password, Role $role = Role::TENANT_USER): User
    {
        $tenantId = $this->currentTenant->id();
        if ($tenantId === null) {
            throw new \RuntimeException('Tenant ID is missing');
        }

        $user = new User;
        $user->tenant_id = $tenantId;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        $this->assignRole->execute($user, $role);

        return $user;
    }
}
