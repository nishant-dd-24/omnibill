<?php

declare(strict_types=1);

namespace Modules\Invoice\Infrastructure\Jobs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Invoice\Application\Adapters\InvoiceRendererInterface;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Shared\Infrastructure\Jobs\TenantAwareJob;

class GenerateInvoicePdfJob extends TenantAwareJob
{
    public function __construct(
        string $tenantId,
        public readonly string $invoiceId
    ) {
        parent::__construct($tenantId);
    }

    public function handle(InvoiceRendererInterface $renderer): void
    {
        $invoice = Invoice::findOrFail($this->invoiceId);

        $pdfBytes = $renderer->render($invoice);

        $path = sprintf('invoices/%s/%s-%s.pdf', $this->tenantId, $invoice->number, Str::random(8));

        Storage::disk('s3')->put($path, $pdfBytes);

        $invoice->update([
            'pdf_url' => $path,
        ]);
    }
}
