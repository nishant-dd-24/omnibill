<?php

declare(strict_types=1);

namespace Modules\Shared\Http\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Base Policy
 *
 * All module-specific policies MUST extend this class.
 * This ensures consistency and explicitly separates role/ownership logic
 * from tenancy logic (which is handled by the Global Scope).
 */
abstract class BasePolicy
{
    use HandlesAuthorization;
}
