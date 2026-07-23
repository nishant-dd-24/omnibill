<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Services;

use Illuminate\Support\Facades\Request;
use Modules\Shared\Domain\Models\AuditLog;

class AuditLogger
{
    /**
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    public function log(
        string $action,
        string $resourceType,
        string $resourceId,
        ?string $tenantId = null,
        ?string $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
