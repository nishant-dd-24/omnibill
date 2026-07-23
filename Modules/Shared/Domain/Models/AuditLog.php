<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class AuditLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new RuntimeException('Audit logs are immutable.');
    }

    public function delete()
    {
        throw new RuntimeException('Audit logs cannot be deleted.');
    }
}
