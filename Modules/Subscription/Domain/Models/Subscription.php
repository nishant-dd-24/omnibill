<?php

declare(strict_types=1);

namespace Modules\Subscription\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class Subscription extends Model
{
    use HasUuids;
    use SoftDeletes;
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'plan_id',
        'status',
        'stripe_id',
        'current_period_start',
        'current_period_end',
        'cancel_at_period_end',
        'canceled_at',
        'ended_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<SubscriptionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }
}
