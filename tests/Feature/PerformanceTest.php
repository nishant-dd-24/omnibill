<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

test('catalog endpoint responds within acceptable threshold', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $start = microtime(true);

    $response = $this->actingAs($user)
        ->withHeader('X-Tenant-ID', $tenant->id)
        ->getJson('/api/v1/plans');

    $duration = microtime(true) - $start;

    $response->assertStatus(200);
    expect($duration)->toBeLessThan(0.5);
});
