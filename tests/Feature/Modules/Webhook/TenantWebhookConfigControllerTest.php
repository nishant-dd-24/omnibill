<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Models\TenantSettings;

uses(RefreshDatabase::class);

function setupWebhookConfigTest(): array
{
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $currentTenant = new CurrentTenant($tenant->id);
    app()->instance(CurrentTenant::class, $currentTenant);
    test()->withHeaders(['X-Tenant-ID' => $tenant->id]);

    return [$tenant->id, $user];
}

it('can get webhook config', function () {
    [$tenantId, $user] = setupWebhookConfigTest();

    TenantSettings::create([
        'tenant_id' => $tenantId,
        'settings' => ['webhook_url' => 'https://example.com/webhook'],
    ]);

    $response = test()->actingAs($user)->getJson('/api/v1/webhook-config');

    $response->assertStatus(200)
        ->assertJsonPath('data.webhook_url', 'https://example.com/webhook');
});

it('can update webhook config', function () {
    [$tenantId, $user] = setupWebhookConfigTest();

    $response = test()->actingAs($user)->putJson('/api/v1/webhook-config', [
        'webhook_url' => 'https://example.com/new-webhook',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.webhook_url', 'https://example.com/new-webhook');

    $this->assertDatabaseHas('tenant_settings', [
        'tenant_id' => $tenantId,
    ]);
});

it('validates webhook url format', function () {
    [$tenantId, $user] = setupWebhookConfigTest();

    $response = test()->actingAs($user)->putJson('/api/v1/webhook-config', [
        'webhook_url' => 'not-a-url',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['webhook_url']]]);
});
