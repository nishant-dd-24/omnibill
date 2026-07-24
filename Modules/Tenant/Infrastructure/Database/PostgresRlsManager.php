<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Database;

use Illuminate\Support\Facades\DB;

class PostgresRlsManager
{
    private function isPgsql(): bool
    {
        try {
            return DB::getDriverName() === 'pgsql';
        } catch (\Exception $e) {
            return false;
        }
    }

    public function setTenantContext(string $tenantId): void
    {
        if (! $this->isPgsql()) {
            return;
        }
        DB::statement("SET omnibill.bypass_rls = 'off'");
        DB::statement("SET omnibill.current_tenant_id = '{$tenantId}'");
    }

    public function clearTenantContext(): void
    {
        if (! $this->isPgsql()) {
            return;
        }
        try {
            DB::statement('RESET omnibill.current_tenant_id');
            DB::statement('RESET omnibill.bypass_rls');
        } catch (\Exception $e) {
        }
    }

    public function bypassRls(): void
    {
        if (! $this->isPgsql()) {
            return;
        }
        DB::statement("SET omnibill.bypass_rls = 'on'");
    }

    /**
     * Executes a callback with RLS bypassed.
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    public function executeBypassed(\Closure $callback): mixed
    {
        $this->bypassRls();
        try {
            return $callback();
        } finally {
            $this->clearTenantContext();
        }
    }
}
