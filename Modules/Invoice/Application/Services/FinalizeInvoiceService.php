<?php

declare(strict_types=1);

namespace Modules\Invoice\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Invoice\Domain\Events\InvoiceFinalized;
use Modules\Invoice\Domain\Models\Invoice;

class FinalizeInvoiceService
{
    public function execute(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw new \DomainException('Only draft invoices can be finalized.');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'open',
                'finalized_at' => now(),
            ]);

            DB::afterCommit(fn () => event(new InvoiceFinalized($invoice)));

            return $invoice;
        });
    }
}
