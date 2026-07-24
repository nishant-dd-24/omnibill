<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Infrastructure\Database\PostgresRlsManager;
use Symfony\Component\HttpFoundation\Response;

class TenantResolutionMiddleware
{
    public function __construct(
        private readonly PostgresRlsManager $rlsManager
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->resolveTenantIdentifier($request);

        // Bind the request-scoped singleton
        app()->instance(CurrentTenant::class, new CurrentTenant($tenantId));

        if ($tenantId !== null) {
            Context::add('tenant_id', $tenantId);
            $this->rlsManager->setTenantContext($tenantId);
        }

        try {
            return $next($request);
        } finally {
            $this->rlsManager->clearTenantContext();
        }
    }

    private function resolveTenantIdentifier(Request $request): ?string
    {
        // 1. Subdomain resolution
        $host = $request->getHost();
        $parts = explode('.', (string) $host);

        // Very basic extraction: assuming {tenant_slug}.omnibill.test
        // In Phase 2, this will be mapped against the Tenant DB to verify active status.
        if (count($parts) >= 3 && $parts[0] !== 'api' && $parts[0] !== 'www') {
            return $parts[0];
        }

        // 2. Fallback to X-Tenant-ID header
        $header = $request->header('X-Tenant-ID');
        if (is_string($header) && $header !== '') {
            return $header;
        }

        return null;
    }
}
