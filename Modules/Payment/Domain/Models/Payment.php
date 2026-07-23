<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class Payment extends Model
{
    use HasUuids;
    use SoftDeletes;
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'customer_id',
        'payment_method_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * @return HasMany<PaymentAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }
}
