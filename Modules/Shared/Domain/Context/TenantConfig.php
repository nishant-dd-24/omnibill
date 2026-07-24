<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Context;

use Illuminate\Config\Repository;
use Modules\Shared\Domain\Contracts\GetTenantSettings;

class TenantConfig
{
    private CurrentTenant $currentTenant;

    private GetTenantSettings $getTenantSettings;

    private ?Repository $repository = null;

    public function __construct(CurrentTenant $currentTenant, GetTenantSettings $getTenantSettings)
    {
        $this->currentTenant = $currentTenant;
        $this->getTenantSettings = $getTenantSettings;
    }

    /**
     * Get the specified tenant configuration value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureRepositoryIsLoaded();

        if (! $this->repository) {
            return $default;
        }

        return $this->repository->get($key, $default);
    }

    /**
     * Determine if the given configuration value exists.
     */
    public function has(string $key): bool
    {
        $this->ensureRepositoryIsLoaded();

        if (! $this->repository) {
            return false;
        }

        return $this->repository->has($key);
    }

    /**
     * Get all of the configuration items for the tenant.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $this->ensureRepositoryIsLoaded();

        if (! $this->repository) {
            return [];
        }

        return $this->repository->all();
    }

    /**
     * Ensure the internal config repository is loaded with the current tenant's settings.
     */
    private function ensureRepositoryIsLoaded(): void
    {
        if ($this->repository !== null) {
            return; // Already loaded
        }

        $tenantId = $this->currentTenant->id();
        if ($tenantId === null) {
            return; // No tenant context available
        }

        $settings = $this->getTenantSettings->execute($tenantId);
        $this->repository = new Repository($settings);
    }
}
