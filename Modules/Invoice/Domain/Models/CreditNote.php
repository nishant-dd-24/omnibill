<?php

declare(strict_types=1);

namespace Modules\Invoice\Domain\Models;

use Database\Factories\CreditNoteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Domain\Models\Traits\TenantScoped;

class CreditNote extends Model
{
    /** @use HasFactory<CreditNoteFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'number',
        'amount',
        'currency',
        'reason',
        'issued_at',
        'pdf_url',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    protected static function newFactory(): CreditNoteFactory
    {
        return CreditNoteFactory::new();
    }
}
