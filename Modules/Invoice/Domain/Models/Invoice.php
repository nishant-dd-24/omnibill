<?php

declare(strict_types=1);

namespace Modules\Invoice\Domain\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'subscription_id',
        'number',
        'status',
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'amount_paid',
        'amount_due',
        'due_date',
        'finalized_at',
        'paid_at',
        'voided_at',
        'pdf_url',
        'metadata',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'finalized_at' => 'datetime',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }

    /**
     * @return HasMany<InvoiceLineItem, $this>
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    /**
     * @return HasMany<CreditNote, $this>
     */
    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }
}
