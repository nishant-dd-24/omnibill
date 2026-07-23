<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Services;

use Modules\Shared\Domain\Exceptions\DomainException;
use Modules\Tenant\Domain\Models\Tenant;

class TenantLifecycleStateMachine
{
    /**
     * @var array<string, array<string>>
     */
    private array $validTransitions = [
        'Pending' => ['Active', 'Cancelled'],
        'Active' => ['PastDue', 'Suspended', 'Cancelled'],
        'PastDue' => ['Active', 'Suspended', 'Cancelled'],
        'Suspended' => ['Active', 'Cancelled'],
        'Cancelled' => [],
    ];

    public function transitionTo(Tenant $tenant, string $newState): void
    {
        $currentState = $tenant->status;

        if (! in_array($newState, $this->validTransitions[$currentState] ?? [], true)) {
            throw new DomainException("Invalid transition from {$currentState} to {$newState}");
        }

        $tenant->status = $newState;
    }
}
