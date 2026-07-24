<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Payment\Domain\Models\Payment;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

function setupPaymentTest(): array
{
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

    $currentTenant = new CurrentTenant($tenant->id);
    app()->instance(CurrentTenant::class, $currentTenant);
    test()->withHeaders(['X-Tenant-ID' => $tenant->id]);

    return [$tenant->id, $user, $customer];
}

it('can list payments', function () {
    [$tenantId, $user, $customer] = setupPaymentTest();

    $payment = Payment::create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'amount' => 1000,
        'currency' => 'usd',
        'status' => 'succeeded',
    ]);

    $response = test()->actingAs($user)->getJson('/api/v1/payments');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $payment->id);
});

it('can show a payment', function () {
    [$tenantId, $user, $customer] = setupPaymentTest();

    $payment = Payment::create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'amount' => 1000,
        'currency' => 'usd',
        'status' => 'succeeded',
    ]);

    $response = test()->actingAs($user)->getJson("/api/v1/payments/{$payment->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $payment->id);
});

it('prevents cross-tenant access to payments', function () {
    [$tenantId, $user] = setupPaymentTest();

    $otherTenant = Tenant::create(['name' => 'Other Tenant', 'status' => 'active']);
    $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);

    // Switch to other tenant to bypass global scope when creating
    app()->instance(CurrentTenant::class, new CurrentTenant($otherTenant->id));
    $payment = Payment::create([
        'tenant_id' => $otherTenant->id,
        'customer_id' => $otherCustomer->id,
        'amount' => 1000,
        'currency' => 'usd',
        'status' => 'succeeded',
    ]);

    // Switch back to original tenant for the request
    app()->instance(CurrentTenant::class, new CurrentTenant($tenantId));

    $response = test()->actingAs($user)->getJson("/api/v1/payments/{$payment->id}");

    $response->assertStatus(404);
});
