<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Jobs\Middleware;

use Illuminate\Support\Facades\Context;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Infrastructure\Database\PostgresRlsManager;

class BindTenantContext
{
    public function handle(mixed $job, callable $next): void
    {
        $hasBoundTenant = app()->bound(CurrentTenant::class);
        $previousTenant = $hasBoundTenant ? app(CurrentTenant::class) : null;
        $rlsManager = app(PostgresRlsManager::class);

        if (is_object($job) && property_exists($job, 'tenantId')) {
            app()->instance(CurrentTenant::class, new CurrentTenant($job->tenantId));
            Context::add('tenant_id', $job->tenantId);
            $rlsManager->setTenantContext($job->tenantId);
        }

        try {
            $next($job);
        } finally {
            if ($previousTenant !== null) {
                app()->instance(CurrentTenant::class, $previousTenant);
                if ($previousTenant->hasTenant()) {
                    $tenantId = $previousTenant->id();
                    if ($tenantId !== null) {
                        $rlsManager->setTenantContext((string) $tenantId);
                    }
                } else {
                    $rlsManager->clearTenantContext();
                }
            } else {
                app()->forgetInstance(CurrentTenant::class);
                $rlsManager->clearTenantContext();
            }
        }
    }
}
