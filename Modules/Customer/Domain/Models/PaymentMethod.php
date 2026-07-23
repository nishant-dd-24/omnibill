<?php

declare(strict_types=1);

namespace Modules\Customer\Domain\Models;

use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'stripe_payment_method_id',
        'type',
        'last4',
        'brand',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'exp_month' => 'integer',
        'exp_year' => 'integer',
    ];

    protected static function newFactory(): PaymentMethodFactory
    {
        return PaymentMethodFactory::new();
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
