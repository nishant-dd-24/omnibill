<?php

declare(strict_types=1);

namespace Modules\Subscription\Domain\Models;

use Database\Factories\PriceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    /** @use HasFactory<PriceFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'plan_id',
        'stripe_price_id',
        'amount',
        'currency',
        'interval',
        'interval_count',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'integer',
        'interval_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): PriceFactory
    {
        return PriceFactory::new();
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
