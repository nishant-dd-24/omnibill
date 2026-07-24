<?php

declare(strict_types=1);

namespace Modules\Notification\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class NotificationLog extends Model
{
    use HasUuids, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'recipient',
        'subject',
        'payload',
        'type',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
