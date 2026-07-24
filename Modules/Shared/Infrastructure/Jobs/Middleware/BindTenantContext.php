<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Jobs\Middleware;

use Modules\Shared\Domain\Context\CurrentTenant;

class BindTenantContext
{
    public function handle(mixed $job, callable $next): void
    {
        if (isset($job->tenantId)) {
            app()->instance(CurrentTenant::class, new CurrentTenant($job->tenantId));
        }

        $next($job);
    }
}
