<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Services\CreateTenantService;
use Modules\Tenant\Application\Services\TenantLifecycleService;
use Modules\Tenant\Domain\Events\TenantActivated;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Services\TenantLifecycleStateMachine;

uses(RefreshDatabase::class);

test('it creates a tenant in pending state', function () {
    $service = new CreateTenantService;

    $tenant = $service->execute(['name' => 'Acme Corp']);

    expect($tenant->name)->toBe('Acme Corp')
        ->and($tenant->status)->toBe('Pending');
});

test('it transitions tenant state and dispatches event', function () {
    Event::fake();

    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Pending']);

    $stateMachine = new TenantLifecycleStateMachine;
    $service = new TenantLifecycleService($stateMachine);

    $service->transitionTo($tenant, 'Active');

    expect($tenant->fresh()->status)->toBe('Active');

    Event::assertDispatched(TenantActivated::class, function ($event) use ($tenant) {
        return $event->tenant->id === $tenant->id;
    });
});
