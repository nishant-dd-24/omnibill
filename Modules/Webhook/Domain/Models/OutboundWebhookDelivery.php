<?php

namespace Modules\Webhook\Domain\Models;

use Modules\Shared\Domain\Models\BaseModel;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class OutboundWebhookDelivery extends BaseModel
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'event_name',
        'payload',
        'response_status',
        'attempts',
        'delivered_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];
}
