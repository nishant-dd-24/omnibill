<?php

namespace Modules\IdentityAccess\Domain\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class Token extends SanctumPersonalAccessToken
{
    // Custom Token model to fit the domain, if necessary.
}
