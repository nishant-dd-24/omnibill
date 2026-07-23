<?php

namespace Modules\IdentityAccess\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\IdentityAccess\Domain\Models\User;

class RevokeAllTokensForTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId
    ) {}

    public function handle(): void
    {
        // Delete all personal access tokens for users of this tenant
        // Bypassing global scope if needed, or explicitly setting it
        $users = User::withoutGlobalScopes()->where('tenant_id', $this->tenantId)->get();

        foreach ($users as $user) {
            $user->tokens()->delete();
        }
    }
}
