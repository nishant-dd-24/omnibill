<?php

declare(strict_types=1);

namespace Modules\Invoice\Infrastructure\Adapters;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Invoice\Application\Adapters\InvoiceRendererInterface;
use Modules\Invoice\Domain\Models\Invoice;

class DompdfInvoiceRenderer implements InvoiceRendererInterface
{
    public function render(Invoice $invoice): string
    {
        // Load the invoice view and pass the invoice data
        $pdf = Pdf::loadView('invoices.default', ['invoice' => $invoice]);

        // Return the raw PDF bytes
        return $pdf->output();
    }
}
