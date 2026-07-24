<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class TenantSettings extends Model
{
    use HasUuids, TenantScoped;

    protected $fillable = [
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted(): void
    {
        $clearCache = function (self $model) {
            Cache::forget("tenant_settings:{$model->tenant_id}");
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }
}
