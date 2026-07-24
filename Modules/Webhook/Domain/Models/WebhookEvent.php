<?php

declare(strict_types=1);

namespace Modules\Webhook\Domain\Models;

use Modules\Shared\Domain\Models\BaseModel;
use Ramsey\Uuid\Uuid;

class WebhookEvent extends BaseModel
{
    protected $table = 'webhook_events';

    protected $fillable = [
        'provider',
        'provider_event_id',
        'event_type',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Generate a new UUIDv7 for the model.
     */
    public function newUniqueId(): string
    {
        return Uuid::uuid7()->toString();
    }
}
