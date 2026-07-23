<?php

declare(strict_types=1);

namespace Modules\Payment\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Domain\Models\Payment;

class ProcessDunning implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        // For demonstration of Dunning and Retry orchestration
        // We find failed payments that haven't reached max retries

        $failedPayments = Payment::where('status', 'failed')
            ->whereHas('attempts', function ($query) {
                $query->havingRaw('COUNT(id) < ?', [3]);
            })
            ->withCount('attempts')
            ->get();

        foreach ($failedPayments as $payment) {
            Log::info("Dunning: Retrying payment {$payment->id} for tenant {$payment->tenant_id}");
            // In a real scenario, this would dispatch a RetryPaymentJob
            // which interfaces with Stripe/Payment Gateway.

            // For now, we update it back to pending to allow a new attempt
            $payment->update(['status' => 'pending']);
        }
    }
}
