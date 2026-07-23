<?php

declare(strict_types=1);

namespace Modules\Customer\Domain\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'stripe_id',
    ];

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    /**
     * @return HasMany<PaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
