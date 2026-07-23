<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Models\TenantSettings;

class UpdateTenantSettingsService
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function execute(Tenant $tenant, array $settings): void
    {
        /** @var TenantSettings $tenantSettings */
        $tenantSettings = $tenant->settings()->firstOrCreate([]);

        $currentSettings = is_array($tenantSettings->settings) ? $tenantSettings->settings : [];
        $tenantSettings->settings = array_merge($currentSettings, $settings);

        $tenantSettings->save();
    }
}
