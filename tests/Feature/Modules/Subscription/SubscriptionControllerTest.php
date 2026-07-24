<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Price;
use Modules\Subscription\Domain\Models\Subscription;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

/**
 * @return array{0: string, 1: User, 2: Customer, 3: Plan, 4: Price}
 */
function setupSubscriptionTest(): array
{
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
    $plan = Plan::factory()->create(['is_active' => true]);
    $price = Price::factory()->create(['plan_id' => $plan->id]);

    $currentTenant = new CurrentTenant($tenant->id);
    app()->instance(CurrentTenant::class, $currentTenant);
    test()->withHeaders(['X-Tenant-ID' => $tenant->id]);

    return [$tenant->id, $user, $customer, $plan, $price];
}

it('can list subscriptions', function () {
    [$tenantId, $user, $customer, $plan, $price] = setupSubscriptionTest();

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'plan_id' => $plan->id,
    ]);

    $response = test()->actingAs($user)->getJson('/api/v1/subscriptions');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $subscription->id);
});

it('can create a subscription', function () {
    [$tenantId, $user, $customer, $plan, $price] = setupSubscriptionTest();

    $response = test()->actingAs($user)
        ->withHeaders(['Idempotency-Key' => Str::uuid()->toString()])
        ->postJson('/api/v1/subscriptions', [
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'items' => [
                ['price_id' => $price->id, 'quantity' => 1],
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.customer_id', $customer->id);
});

it('fails validation on store if required parameters are missing', function () {
    [$tenantId, $user, $customer, $plan, $price] = setupSubscriptionTest();

    $response = test()->actingAs($user)
        ->postJson('/api/v1/subscriptions', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'error' => [
                'details' => ['customer_id', 'plan_id', 'items'],
            ],
        ]);
});

it('can show a subscription', function () {
    [$tenantId, $user, $customer, $plan, $price] = setupSubscriptionTest();

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'plan_id' => $plan->id,
    ]);

    $response = test()->actingAs($user)
        ->getJson("/api/v1/subscriptions/{$subscription->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $subscription->id);
});

it('prevents accessing cross-tenant subscription (IDOR)', function () {
    [$tenantId, $user, $customer, $plan, $price] = setupSubscriptionTest();

    $otherTenant = Tenant::create(['name' => 'Other Tenant', 'status' => 'active']);
    $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherSubscription = Subscription::factory()->create([
        'tenant_id' => $otherTenant->id,
        'customer_id' => $otherCustomer->id,
        'plan_id' => $plan->id,
    ]);

    $response = test()->actingAs($user)
        ->getJson("/api/v1/subscriptions/{$otherSubscription->id}");

    $response->assertStatus(404);
});

it('can update a subscription', function () {
    [$tenantId, $user, $customer, $plan, $price] = setupSubscriptionTest();

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'plan_id' => $plan->id,
    ]);

    $newPlan = Plan::factory()->create(['is_active' => true]);
    $newPrice = Price::factory()->create(['plan_id' => $newPlan->id]);

    $response = test()->actingAs($user)
        ->putJson("/api/v1/subscriptions/{$subscription->id}", [
            'plan_id' => $newPlan->id,
            'items' => [
                ['price_id' => $newPrice->id, 'quantity' => 2],
            ],
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.plan_id', $newPlan->id);
});

it('can cancel a subscription', function () {
    [$tenantId, $user, $customer, $plan, $price] = setupSubscriptionTest();

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->deleteJson("/api/v1/subscriptions/{$subscription->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'canceled');
});
