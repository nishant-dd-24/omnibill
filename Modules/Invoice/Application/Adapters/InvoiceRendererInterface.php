<?php

declare(strict_types=1);

namespace Modules\Invoice\Application\Adapters;

use Modules\Invoice\Domain\Models\Invoice;

interface InvoiceRendererInterface
{
    /**
     * Render the invoice to a PDF.
     *
     * @return string Raw PDF bytes
     */
    public function render(Invoice $invoice): string;
}
