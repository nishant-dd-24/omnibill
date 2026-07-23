<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Modules\Tenant\Domain\Models\Tenant;

class PurgeDeletedTenantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $threshold = Carbon::now()->subDays(30);

        Tenant::onlyTrashed()
            ->where('deleted_at', '<', $threshold)
            ->forceDelete();
    }
}
