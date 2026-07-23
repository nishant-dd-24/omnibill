<?php

namespace App\Logging;

use Illuminate\Support\Facades\Auth;
use Modules\Shared\Domain\Context\CorrelationId;
use Modules\Shared\Domain\Context\CurrentTenant;
use Monolog\LogRecord;

class OmniBillLogProcessor
{
    /**
     * Add OmniBill standard fields to every log record.
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $tenantId = app()->bound(CurrentTenant::class) ? app(CurrentTenant::class)->id() : null;
        $correlationId = app()->bound(CorrelationId::class) ? app(CorrelationId::class)->id() : null;
        $userId = Auth::check() ? Auth::id() : null;

        $extraFields = [
            'tenant_id' => $tenantId,
            'correlation_id' => $correlationId,
            'user_id' => $userId,
        ];

        return $record->with(extra: array_merge($record->extra, $extraFields));
    }
}
