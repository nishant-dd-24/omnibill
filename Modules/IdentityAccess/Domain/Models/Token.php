<?php

declare(strict_types=1);

namespace Modules\IdentityAccess\Domain\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class Token extends SanctumPersonalAccessToken
{
    // Custom Token model to fit the domain, if necessary.
}
