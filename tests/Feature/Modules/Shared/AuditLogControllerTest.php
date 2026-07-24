<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Shared\Domain\Models\AuditLog;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

function setupAuditLogTest(): array
{
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $currentTenant = new CurrentTenant($tenant->id);
    app()->instance(CurrentTenant::class, $currentTenant);
    test()->withHeaders(['X-Tenant-ID' => $tenant->id]);

    return [$tenant->id, $user];
}

it('can list audit logs', function () {
    [$tenantId, $user] = setupAuditLogTest();

    $auditLog = new AuditLog([
        'tenant_id' => $tenantId,
        'user_id' => $user->id,
        'action' => 'created',
        'resource_type' => 'invoice',
        'resource_id' => '123',
    ]);
    $auditLog->save();

    $response = test()->actingAs($user)->getJson('/api/v1/audit-logs');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $auditLog->id);
});

it('can show an audit log', function () {
    [$tenantId, $user] = setupAuditLogTest();

    $auditLog = new AuditLog([
        'tenant_id' => $tenantId,
        'user_id' => $user->id,
        'action' => 'created',
        'resource_type' => 'invoice',
        'resource_id' => '123',
    ]);
    $auditLog->save();

    $response = test()->actingAs($user)->getJson("/api/v1/audit-logs/{$auditLog->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $auditLog->id);
});

it('prevents cross-tenant access to audit logs', function () {
    [$tenantId, $user] = setupAuditLogTest();

    $otherTenant = Tenant::create(['name' => 'Other Tenant', 'status' => 'active']);
    $auditLog = new AuditLog([
        'tenant_id' => $otherTenant->id,
        'user_id' => $user->id,
        'action' => 'created',
        'resource_type' => 'invoice',
        'resource_id' => '123',
    ]);
    $auditLog->save();

    $response = test()->actingAs($user)->getJson("/api/v1/audit-logs/{$auditLog->id}");

    $response->assertStatus(404);
});
