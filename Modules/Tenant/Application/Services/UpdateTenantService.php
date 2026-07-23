<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Modules\Tenant\Domain\Models\Tenant;

class UpdateTenantService
{
    /**
     * @param  array{name?: string}  $data
     */
    public function execute(Tenant $tenant, array $data): Tenant
    {
        if (isset($data['name'])) {
            $tenant->name = $data['name'];
            $tenant->save();
        }

        return $tenant;
    }
}
