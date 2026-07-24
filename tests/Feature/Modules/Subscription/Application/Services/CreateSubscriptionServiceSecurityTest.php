<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Domain\Models\Customer;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Subscription\Application\Services\CreateSubscriptionService;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Price;
use Modules\Subscription\Domain\Services\SubscriptionStateMachine;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

it('prevents creating a subscription for a customer belonging to another tenant', function () {
    $tenantA = Tenant::create(['name' => 'Tenant A', 'status' => 'Active']);
    $tenantB = Tenant::create(['name' => 'Tenant B', 'status' => 'Active']);

    app()->instance(CurrentTenant::class, new CurrentTenant((string) $tenantA->id));

    $customerB = Customer::factory()->create(['tenant_id' => $tenantB->id]);

    $plan = Plan::factory()->create();

    $service = new CreateSubscriptionService(new SubscriptionStateMachine);

    expect(fn () => $service->execute($tenantA->id, $customerB->id, $plan, []))
        ->toThrow(ModelNotFoundException::class);
});

it('prevents using a price that does not belong to the selected plan', function () {
    $tenant = Tenant::create(['name' => 'Tenant', 'status' => 'Active']);
    app()->instance(CurrentTenant::class, new CurrentTenant((string) $tenant->id));
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

    $planA = Plan::factory()->create();
    $planB = Plan::factory()->create();

    $priceB = Price::factory()->create(['plan_id' => $planB->id]);

    $service = new CreateSubscriptionService(new SubscriptionStateMachine);

    expect(fn () => $service->execute($tenant->id, $customer->id, $planA, [
        ['price_id' => $priceB->id],
    ]))->toThrow(DomainException::class, "Price {$priceB->id} does not belong to the selected plan.");
});
