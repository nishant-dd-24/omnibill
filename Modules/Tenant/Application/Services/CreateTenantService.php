<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Modules\Tenant\Domain\Models\Tenant;

class CreateTenantService
{
    /**
     * @param  array{name: string}  $data
     */
    public function execute(array $data): Tenant
    {
        return Tenant::create([
            'name' => $data['name'],
            'status' => 'Pending',
        ]);
    }
}
