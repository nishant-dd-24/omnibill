<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Models;

class OutboxEvent extends BaseModel
{
    protected $table = 'outbox_events';

    protected $fillable = [
        'event_name',
        'payload',
        'tenant_id',
        'correlation_id',
        'queue',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
