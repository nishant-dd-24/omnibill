<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * @return HasOne<TenantSettings, $this>
     */
    public function settings(): HasOne
    {
        return $this->hasOne(TenantSettings::class);
    }

    /**
     * @return HasOne<TenantPlanAssignment, $this>
     */
    public function planAssignment(): HasOne
    {
        return $this->hasOne(TenantPlanAssignment::class);
    }
}
