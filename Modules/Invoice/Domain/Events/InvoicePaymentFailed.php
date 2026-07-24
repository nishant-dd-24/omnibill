<?php

declare(strict_types=1);

namespace Modules\Invoice\Domain\Events;

use Modules\Invoice\Domain\Models\Invoice;

class InvoicePaymentFailed
{
    public function __construct(
        public readonly Invoice $invoice,
    ) {}
}
