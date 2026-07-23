<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Tenant\Domain\Models\Tenant;

class TenantSuspended
{
    use Dispatchable;

    public function __construct(
        public readonly Tenant $tenant
    ) {}
}
