<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Shared\Domain\Scopes\TenantScope;
use Modules\Tenant\Infrastructure\Database\PostgresRlsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('enforces row level security at the database layer', function () {
    // Only run this test if using Postgres
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('RLS tests require PostgreSQL.');
    }

    $rlsManager = app(PostgresRlsManager::class);

    // We must bypass RLS initially to set up test data since we don't have a tenant context yet
    $rlsManager->executeBypassed(function () {
        // Create Tenant A and User A
        $tenantAId = (string) \Illuminate\Support\Str::uuid();
        DB::table('tenants')->insert(['id' => $tenantAId, 'name' => 'Tenant A', 'created_at' => now(), 'updated_at' => now()]);
        
        $userA = User::factory()->create([
            'tenant_id' => $tenantAId,
            'email' => 'a@example.com'
        ]);

        // Create Tenant B and User B
        $tenantBId = (string) \Illuminate\Support\Str::uuid();
        DB::table('tenants')->insert(['id' => $tenantBId, 'name' => 'Tenant B', 'created_at' => now(), 'updated_at' => now()]);
        
        $userB = User::factory()->create([
            'tenant_id' => $tenantBId,
            'email' => 'b@example.com'
        ]);
        
        // Assert they both exist
        expect(User::withoutGlobalScope(TenantScope::class)->count())->toBeGreaterThanOrEqual(2);

        // Test 1: Set context to Tenant A
        $rlsManager->setTenantContext($tenantAId);
    });

    // We are outside executeBypassed, but context was set to Tenant A.
    // Wait, executeBypassed clears the context in the finally block!
    // So we must set it again here.
    $tenantAId = DB::table('tenants')->where('name', 'Tenant A')->value('id');
    $rlsManager->setTenantContext($tenantAId);

    // Query using raw DB should only return Tenant A's users
    $rawUsers = DB::select('SELECT * FROM users');
    
    // We might have the global seeder user in there too? Wait, the seeder user has NO tenant_id or some other tenant_id. 
    // Since RLS is strict on tenant_id, we only see users with Tenant A's ID.
    $hasTenantB = false;
    foreach ($rawUsers as $u) {
        if ($u->email === 'b@example.com') {
            $hasTenantB = true;
        }
    }
    expect($hasTenantB)->toBeFalse();

    // Query using Eloquent bypassing scope should STILL only return Tenant A's users
    $eloquentUsers = User::withoutGlobalScope(TenantScope::class)->get();
    $hasTenantB = false;
    foreach ($eloquentUsers as $u) {
        if ($u->email === 'b@example.com') {
            $hasTenantB = true;
        }
    }
    expect($hasTenantB)->toBeFalse();

    // Test 2: Clear context (Simulate forgotten context or raw CLI access)
    $rlsManager->clearTenantContext();

    // Query using Eloquent bypassing scope should return NOTHING from tenant tables because bypass_rls is off
    $noContextUsers = User::withoutGlobalScope(TenantScope::class)->get();
    expect($noContextUsers->count())->toBe(0);

    // Test 3: Bypass RLS (Simulate Super Admin)
    $rlsManager->executeBypassed(function () {
        $allUsers = User::withoutGlobalScope(TenantScope::class)->get();
        // Should find both user A and user B
        $foundA = false;
        $foundB = false;
        foreach ($allUsers as $u) {
            if ($u->email === 'a@example.com') $foundA = true;
            if ($u->email === 'b@example.com') $foundB = true;
        }
        expect($foundA)->toBeTrue();
        expect($foundB)->toBeTrue();
    });
});
