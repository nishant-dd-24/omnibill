<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Jobs\Middleware;

use Modules\Shared\Domain\Context\CurrentTenant;

class BindTenantContext
{
    public function handle(mixed $job, callable $next): void
    {
        $hasBoundTenant = app()->bound(CurrentTenant::class);
        $previousTenant = $hasBoundTenant ? app(CurrentTenant::class) : null;

        if (is_object($job) && property_exists($job, 'tenantId')) {
            app()->instance(CurrentTenant::class, new CurrentTenant($job->tenantId));
        }

        try {
            $next($job);
        } finally {
            if ($previousTenant !== null) {
                app()->instance(CurrentTenant::class, $previousTenant);
            } else {
                app()->forgetInstance(CurrentTenant::class);
            }
        }
    }
}
