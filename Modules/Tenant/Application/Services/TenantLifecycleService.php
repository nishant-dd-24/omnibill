<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Services\TenantLifecycleStateMachine;

// use Modules\Tenant\Domain\Events\TenantActivated; // Will add later

class TenantLifecycleService
{
    public function __construct(
        private readonly TenantLifecycleStateMachine $stateMachine
    ) {}

    public function transitionTo(Tenant $tenant, string $newState): void
    {
        DB::transaction(function () use ($tenant, $newState) {
            $this->stateMachine->transitionTo($tenant, $newState);
            $tenant->save();

            // Note: Domain events will be dispatched here in a later step
        });
    }
}
