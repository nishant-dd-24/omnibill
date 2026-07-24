<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Infrastructure\Database\PostgresRlsManager;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PostgresRlsManager::class)->executeBypassed(function () {
            // User::factory(10)->create();

            $tenant = Tenant::create([
                'name' => 'Acme Corp',
            ]);

            $user = User::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

            $user->roles()->create([
                'role' => Role::SUPER_ADMIN->value,
            ]);
        });
    }
}
