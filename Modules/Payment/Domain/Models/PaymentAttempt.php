<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class PaymentAttempt extends Model
{
    use HasUuids;
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'attempt_number',
        'status',
        'gateway_response',
        'attempted_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'attempted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
