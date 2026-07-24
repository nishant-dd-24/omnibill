<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Fake;

use Modules\Shared\Domain\Contracts\GetTenantSettings;

class InMemoryTenantSettings implements GetTenantSettings
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $settings = [];

    /**
     * Set fake settings for a tenant (used for testing).
     *
     * @param  array<string, mixed>  $settings
     */
    public function setForTenant(string $tenantId, array $settings): void
    {
        $this->settings[$tenantId] = $settings;
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(string $tenantId): array
    {
        return $this->settings[$tenantId] ?? [];
    }
}
