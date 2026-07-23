<?php

namespace Tests\Modules\IdentityAccess\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\User;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Assume tenant middleware is bypassed or mock handled for feature tests
        // For this test, we will create a tenant and set header
    }

    public function test_tenant_admin_can_create_user(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => Uuid::uuid7()->toString(),
        ]);
        $admin->roles()->create(['role' => Role::TENANT_ADMIN->value]);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'role' => 'tenant_user',
        ], [
            'X-Tenant-ID' => $admin->tenant_id,
        ]);

        $response->dump();

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'New User');
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'tenant_id' => $admin->tenant_id]);
    }
}
