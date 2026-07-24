<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Customer\Domain\Events\CustomerCreated;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Tenant\Domain\Models\Tenant;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('can list customers for a tenant', function () {
    /** @var TestCase $this */
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($user);

    Customer::factory()->count(3)->create(['tenant_id' => $tenant->id]);
    Customer::factory()->count(2)->create(['tenant_id' => Tenant::create(['name' => 'Other', 'status' => 'Active'])->id]);

    $response = $this->withHeader('X-Tenant-ID', $tenant->id)->getJson('/api/customers');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('can create a customer', function () {
    /** @var TestCase $this */
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($user);

    Event::fake([CustomerCreated::class]);

    $response = $this->withHeader('X-Tenant-ID', $tenant->id)->postJson('/api/customers', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'stripe_id' => 'cus_12345',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'John Doe');

    expect(Customer::where([
        'tenant_id' => $tenant->id,
        'email' => 'john@example.com',
    ])->exists())->toBeTrue();

    Event::assertDispatched(CustomerCreated::class);
});

it('requires valid data for customer creation', function () {
    /** @var TestCase $this */
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($user);

    $response = $this->withHeader('X-Tenant-ID', $tenant->id)->postJson('/api/customers', [
        'name' => '',
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email'], 'error.details');
});

it('requires valid data for customer update', function () {
    /** @var TestCase $this */
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($user);

    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->withHeader('X-Tenant-ID', $tenant->id)->putJson("/api/customers/{$customer->id}", [
        'name' => '',
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email'], 'error.details');
});

