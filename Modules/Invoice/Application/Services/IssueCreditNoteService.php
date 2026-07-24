<?php

declare(strict_types=1);

namespace Modules\Invoice\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Invoice\Domain\Models\CreditNote;
use Modules\Invoice\Domain\Models\Invoice;

class IssueCreditNoteService
{
    public function execute(Invoice $invoice, int $amount, ?string $reason = null): CreditNote
    {
        if ($invoice->status === 'draft') {
            throw new \DomainException('Cannot issue credit note for draft invoice.');
        }

        if ($amount > $invoice->amount_due) {
            throw new \DomainException('Credit note amount cannot exceed invoice amount due.');
        }

        return DB::transaction(function () use ($invoice, $amount, $reason) {
            /** @var CreditNote $creditNote */
            $creditNote = CreditNote::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'number' => 'CN-'.strtoupper(Str::random(8)),
                'amount' => $amount,
                'currency' => $invoice->currency,
                'reason' => $reason,
                'issued_at' => now(),
            ]);

            return $creditNote;
        });
    }
}
