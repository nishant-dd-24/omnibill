<?php

declare(strict_types=1);

namespace Modules\Payment\Application\Console\Commands;

use Illuminate\Console\Command;
use Modules\Payment\Application\Jobs\ProcessTenantDunningJob;
use Modules\Shared\Domain\Scopes\TenantScope;
use Modules\Tenant\Domain\Models\Tenant;

class OrchestrateDunningCommand extends Command
{
    protected $signature = 'omnibill:orchestrate-dunning';

    protected $description = 'Dispatches dunning jobs for all active tenants';

    public function handle(): int
    {
        $this->info('Orchestrating dunning jobs...');

        // We bypass the TenantScope because this is a global command.
        $tenants = Tenant::withoutGlobalScope(TenantScope::class)
            ->where('status', 'Active')
            ->get();

        foreach ($tenants as $tenant) {
            ProcessTenantDunningJob::dispatch((string) $tenant->id);
        }

        $this->info('Dunning orchestration complete.');

        return Command::SUCCESS;
    }
}
