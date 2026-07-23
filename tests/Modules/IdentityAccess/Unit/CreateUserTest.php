<?php

namespace Tests\Modules\IdentityAccess\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Modules\IdentityAccess\Application\Services\AssignRole;
use Modules\IdentityAccess\Application\Services\CreateUser;
use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Shared\Domain\Context\CurrentTenant;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_and_assigns_a_role(): void
    {
        $tenantId = Uuid::uuid7()->toString();

        $currentTenant = Mockery::mock(CurrentTenant::class);
        $currentTenant->shouldReceive('id')->andReturn($tenantId);
        $currentTenant->shouldReceive('hasTenant')->andReturn(true);

        $this->app->instance(CurrentTenant::class, $currentTenant);

        $assignRole = Mockery::spy(AssignRole::class);

        $service = new CreateUser($assignRole, $currentTenant);

        $user = $service->execute('John Doe', 'john@example.com', 'password', Role::TENANT_USER);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals($tenantId, $user->tenant_id);
        $this->assertTrue(Hash::check('password', $user->password));

        $assignRole->shouldHaveReceived('execute')->with($user, Role::TENANT_USER);
    }
}
