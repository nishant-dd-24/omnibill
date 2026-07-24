<?php

declare(strict_types=1);

namespace Modules\IdentityAccess\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\IdentityAccess\Domain\Models\User;

class EmailVerified
{
    use Dispatchable;

    public function __construct(
        public readonly User $user
    ) {}
}
