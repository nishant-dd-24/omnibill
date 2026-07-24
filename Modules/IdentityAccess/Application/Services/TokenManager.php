<?php

declare(strict_types=1);

namespace Modules\IdentityAccess\Application\Services;

use Laravel\Sanctum\NewAccessToken;
use Modules\IdentityAccess\Domain\Models\User;

class TokenManager
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function issue(User $user, string $name, array $abilities = ['*']): NewAccessToken
    {
        return $user->createToken($name, $abilities);
    }

    public function revokeAll(User $user): void
    {
        $user->tokens()->delete();
    }

    public function revokeCurrent(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
