<?php

declare(strict_types=1);

namespace Modules\Notification\Infrastructure\Jobs;

use Illuminate\Support\Facades\Mail;
use Modules\Customer\Domain\Models\Customer;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Notification\Domain\Models\NotificationLog;
use Modules\Notification\Infrastructure\Mail\PaymentFailedMailable;
use Modules\Shared\Infrastructure\Jobs\TenantAwareJob;

class SendPaymentFailedEmailJob extends TenantAwareJob
{
    public function __construct(
        string $tenantId,
        public readonly string $invoiceId,
    ) {
        parent::__construct($tenantId);
    }

    public function handle(): void
    {
        $invoice = Invoice::findOrFail($this->invoiceId);
        $customer = Customer::findOrFail($invoice->customer_id);

        try {
            Mail::to($customer->email)->send(new PaymentFailedMailable($invoice));

            NotificationLog::create([
                'tenant_id' => $this->tenantId,
                'customer_id' => $customer->id,
                'type' => 'payment_failed',
                'status' => 'sent',
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            NotificationLog::create([
                'tenant_id' => $this->tenantId,
                'customer_id' => $customer->id,
                'type' => 'payment_failed',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
