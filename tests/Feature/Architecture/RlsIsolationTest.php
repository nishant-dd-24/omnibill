<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Shared\Domain\Scopes\TenantScope;
use Modules\Tenant\Infrastructure\Database\PostgresRlsManager;

uses(RefreshDatabase::class);

it('enforces row level security at the database layer', function () {
    // Only run this test if using Postgres
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('RLS tests require PostgreSQL.');
    }

    $rlsManager = app(PostgresRlsManager::class);

    // We must bypass RLS initially to set up test data since we don't have a tenant context yet
    $rlsManager->executeBypassed(function () use ($rlsManager) {
        // Create Tenant A and User A
        $tenantAId = (string) Str::uuid();
        DB::table('tenants')->insert(['id' => $tenantAId, 'name' => 'Tenant A', 'created_at' => now(), 'updated_at' => now()]);

        $userA = User::factory()->create([
            'tenant_id' => $tenantAId,
            'email' => 'a@example.com',
        ]);

        // Create Tenant B and User B
        $tenantBId = (string) Str::uuid();
        DB::table('tenants')->insert(['id' => $tenantBId, 'name' => 'Tenant B', 'created_at' => now(), 'updated_at' => now()]);

        $userB = User::factory()->create([
            'tenant_id' => $tenantBId,
            'email' => 'b@example.com',
        ]);

        // Assert they both exist
        expect(User::withoutGlobalScope(TenantScope::class)->count())->toBeGreaterThanOrEqual(2);
    });

    // Test 1: Set context to Tenant A
    $tenantAId = DB::table('tenants')->where('name', 'Tenant A')->value('id');
    $tenantBId = DB::table('tenants')->where('name', 'Tenant B')->value('id');
    $rlsManager->setTenantContext($tenantAId);

    // SELECT
    $rawUsers = DB::select('SELECT * FROM users');
    $hasTenantB = false;
    foreach ($rawUsers as $u) {
        if ($u->email === 'b@example.com') {
            $hasTenantB = true;
        }
    }
    expect($hasTenantB)->toBeFalse();

    $eloquentUsers = User::withoutGlobalScope(TenantScope::class)->get();
    $hasTenantB = false;
    foreach ($eloquentUsers as $u) {
        if ($u->email === 'b@example.com') {
            $hasTenantB = true;
        }
    }
    expect($hasTenantB)->toBeFalse();

    // UPDATE: Attempt to update Tenant B's user while in Tenant A's context
    // This should affect 0 rows because Tenant B's rows are invisible to Tenant A.
    $updatedCount = DB::update('UPDATE users SET name = ? WHERE email = ?', ['Hacked', 'b@example.com']);
    expect($updatedCount)->toBe(0);

    // DELETE: Attempt to delete Tenant B's user
    $deletedCount = DB::delete('DELETE FROM users WHERE email = ?', ['b@example.com']);
    expect($deletedCount)->toBe(0);

    // INSERT: Attempt to insert a user for Tenant B while in Tenant A's context
    // PostgreSQL RLS WITH CHECK will block this if the policy is configured for ALL
    // Our policy is: (tenant_id = current_tenant_id)
    $insertException = null;
    try {
        DB::transaction(function () use ($tenantBId) {
            DB::insert('INSERT INTO users (id, tenant_id, name, email, password) VALUES (?, ?, ?, ?, ?)', [
                (string) Str::uuid(),
                $tenantBId, // using Tenant B's ID
                'Hacker',
                'hacker@example.com',
                'secret'
            ]);
        });
    } catch (\Exception $e) {
        $insertException = $e;
    }
    expect($insertException)->not->toBeNull()
        ->and($insertException->getMessage())->toContain('row-level security');

    // Test 2: Clear context (Simulate forgotten context or raw CLI access)
    $rlsManager->clearTenantContext();

    // Query using Eloquent bypassing scope should return NOTHING from tenant tables because bypass_rls is off
    $noContextUsers = User::withoutGlobalScope(TenantScope::class)->get();
    expect($noContextUsers->count())->toBe(0);

    // UPDATE/DELETE without context should affect 0 rows
    $updatedCountNoContext = DB::update('UPDATE users SET name = ?', ['Hacked']);
    expect($updatedCountNoContext)->toBe(0);
    
    $deletedCountNoContext = DB::delete('DELETE FROM users');
    expect($deletedCountNoContext)->toBe(0);

    // Test 3: Bypass RLS (Simulate Super Admin)
    $rlsManager->executeBypassed(function () use ($tenantBId) {
        $allUsers = User::withoutGlobalScope(TenantScope::class)->get();
        // Should find both user A and user B
        $foundA = false;
        $foundB = false;
        foreach ($allUsers as $u) {
            if ($u->email === 'a@example.com') {
                $foundA = true;
            }
            if ($u->email === 'b@example.com') {
                $foundB = true;
            }
        }
        expect($foundA)->toBeTrue();
        expect($foundB)->toBeTrue();

        // UPDATE should now work
        $updated = DB::update('UPDATE users SET name = ? WHERE email = ?', ['AdminUpdated', 'b@example.com']);
        expect($updated)->toBe(1);
        
        // DELETE should now work
        $deleted = DB::delete('DELETE FROM users WHERE email = ?', ['b@example.com']);
        expect($deleted)->toBe(1);
    });
});
