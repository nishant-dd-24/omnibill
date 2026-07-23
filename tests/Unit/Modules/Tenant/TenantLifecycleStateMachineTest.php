<?php

use Modules\Shared\Domain\Exceptions\DomainException;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Services\TenantLifecycleStateMachine;

test('it can transition from pending to active', function () {
    $tenant = new Tenant(['status' => 'Pending']);
    $stateMachine = new TenantLifecycleStateMachine();

    $stateMachine->transitionTo($tenant, 'Active');

    expect($tenant->status)->toBe('Active');
});

test('it throws domain exception on invalid transition', function () {
    $tenant = new Tenant(['status' => 'Pending']);
    $stateMachine = new TenantLifecycleStateMachine();

    $stateMachine->transitionTo($tenant, 'Suspended');
})->throws(DomainException::class);
