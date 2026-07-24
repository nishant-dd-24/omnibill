<?php

namespace Modules\Shared\Domain\Contracts;

interface GetTenantSettings
{
    /**
     * Retrieve the settings for a given tenant ID.
     *
     * @return array<string, mixed>
     */
    public function execute(string $tenantId): array;
}
