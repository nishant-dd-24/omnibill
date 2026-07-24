<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Modules\Shared\Domain\Contracts\GetTenantSettings;
use Modules\Tenant\Domain\Models\TenantSettings;

class CachingGetTenantSettings implements GetTenantSettings
{
    /**
     * @return array<string, mixed>
     */
    public function execute(string $tenantId): array
    {
        $cacheKey = "tenant_settings:{$tenantId}";
        $ttl = 3600; // 1 hour

        return Cache::remember($cacheKey, $ttl, function () use ($tenantId) {
            $settings = TenantSettings::where('tenant_id', $tenantId)->first();

            return $settings ? $settings->toArray() : [];
        });
    }
}
