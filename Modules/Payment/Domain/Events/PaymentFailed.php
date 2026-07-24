<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PaymentFailed
{
    use Dispatchable;

    public function __construct(
        public readonly string $paymentId,
        public readonly string $tenantId,
        public readonly mixed $errorReason = null
    ) {}
}
