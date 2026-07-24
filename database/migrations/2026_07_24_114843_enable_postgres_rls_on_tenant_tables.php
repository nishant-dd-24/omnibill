<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tenantTables = [
        'users',
        'tenant_settings',
        'tenant_plan_assignments',
        'customers',
        'payment_methods',
        'subscriptions',
        'invoices',
        'credit_notes',
        'payments',
        'payment_attempts',
        'notification_logs',
        'audit_logs',
        'outbox_events',
        'outbound_webhook_deliveries',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For testing environments (e.g. SQLite), RLS is not supported.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::transaction(function () {
            foreach ($this->tenantTables as $table) {
                // Enable RLS on the table
                DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY;");
                // Force RLS even for the table owner (Laravel connection user)
                DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY;");

                // Create a unified policy for all operations
                // The current_setting(..., true) makes it return null instead of erroring if not set.
                // It safely handles nullable tenant_id columns (like audit_logs, outbox_events).
                $policy = <<<SQL
                    CREATE POLICY tenant_isolation_policy ON {$table}
                        FOR ALL
                        TO PUBLIC
                        USING (
                            current_setting('omnibill.bypass_rls', true) = 'on'
                            OR 
                            (tenant_id IS NULL AND current_setting('omnibill.bypass_rls', true) = 'on')
                            OR
                            (tenant_id IS NOT NULL AND tenant_id = NULLIF(current_setting('omnibill.current_tenant_id', true), '')::uuid)
                        );
SQL;
                DB::statement($policy);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::transaction(function () {
            foreach ($this->tenantTables as $table) {
                DB::statement("DROP POLICY IF EXISTS tenant_isolation_policy ON {$table};");
                DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY;");
            }
        });
    }
};
