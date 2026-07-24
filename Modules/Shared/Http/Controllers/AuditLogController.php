<?php

declare(strict_types=1);

namespace Modules\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Shared\Domain\Models\AuditLog;
use Modules\Shared\Http\Resources\AuditLogResource;

class AuditLogController extends Controller
{
    public function index(CurrentTenant $currentTenant): AnonymousResourceCollection
    {
        $auditLogs = AuditLog::where('tenant_id', $currentTenant->id())
            ->orderBy('id', 'desc')
            ->cursorPaginate();

        return AuditLogResource::collection($auditLogs);
    }

    public function show(string $id, CurrentTenant $currentTenant): AuditLogResource
    {
        $auditLog = AuditLog::where('tenant_id', $currentTenant->id())
            ->findOrFail($id);

        return new AuditLogResource($auditLog);
    }
}
