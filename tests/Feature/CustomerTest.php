<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Customer\Domain\Events\CustomerCreated;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $this->user = clone User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
    $this->withoutExceptionHandling();
});

it('can list customers for a tenant', function () {
    Customer::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);
    Customer::factory()->count(2)->create(['tenant_id' => Tenant::create(['name' => 'Other', 'status' => 'Active'])->id]);

    $response = $this->withHeader('X-Tenant-ID', $this->tenant->id)->getJson('/api/customers');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('can create a customer', function () {
    Event::fake();

    $response = $this->withHeader('X-Tenant-ID', $this->tenant->id)->postJson('/api/customers', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'stripe_id' => 'cus_12345',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'John Doe');

    $this->assertDatabaseHas('customers', [
        'tenant_id' => $this->tenant->id,
        'email' => 'john@example.com',
    ]);

    Event::assertDispatched(CustomerCreated::class);
});
