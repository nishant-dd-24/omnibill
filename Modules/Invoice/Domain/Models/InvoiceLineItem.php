<?php

declare(strict_types=1);

namespace Modules\Invoice\Domain\Models;

use Database\Factories\InvoiceLineItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLineItem extends Model
{
    /** @use HasFactory<InvoiceLineItemFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_amount',
        'subtotal',
        'tax_amount',
        'total',
        'currency',
        'period_start',
        'period_end',
        'metadata',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function newFactory(): InvoiceLineItemFactory
    {
        return InvoiceLineItemFactory::new();
    }

    protected static function booted(): void
    {
        static::updating(function (InvoiceLineItem $item) {
            if ($item->invoice && $item->invoice->status !== 'draft') {
                throw new \DomainException('Cannot modify line items of a finalized invoice.');
            }
        });

        static::deleting(function (InvoiceLineItem $item) {
            if ($item->invoice && $item->invoice->status !== 'draft') {
                throw new \DomainException('Cannot delete line items of a finalized invoice.');
            }
        });
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
