<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Tenant\Domain\Models\Tenant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('allows super admins to view pulse dashboard', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $user = clone User::factory()->create(['tenant_id' => $tenant->id]);
    $user->roles()->create(['role' => Role::SUPER_ADMIN->value]);

    $response = actingAs($user)->get('/pulse');

    $response->assertStatus(200);
});

it('prevents regular users from viewing pulse dashboard', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $user = clone User::factory()->create(['tenant_id' => $tenant->id]);
    $user->roles()->create(['role' => Role::TENANT_USER->value]);

    $response = actingAs($user)->get('/pulse');

    $response->assertStatus(403);
});

it('prevents unauthenticated users from viewing pulse dashboard', function () {
    $response = get('/pulse');

    // Pulse usually returns 403 or redirects
    $response->assertStatus(403);
});

it('returns healthy status on health check json route', function () {
    $response = getJson('/api/v1/health');

    $response->assertStatus(200);
});
