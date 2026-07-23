<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Tenant\Domain\Events\TenantActivated;
use Modules\Tenant\Domain\Events\TenantCancelled;
use Modules\Tenant\Domain\Events\TenantSuspended;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Services\TenantLifecycleStateMachine;

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

            match ($newState) {
                'Active' => TenantActivated::dispatch($tenant),
                'Suspended' => TenantSuspended::dispatch($tenant),
                'Cancelled' => TenantCancelled::dispatch($tenant),
                default => null,
            };
        });
    }
}
