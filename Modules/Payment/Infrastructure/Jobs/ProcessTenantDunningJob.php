<?php

declare(strict_types=1);

namespace Modules\Payment\Infrastructure\Jobs;

use Modules\Payment\Domain\Models\Payment;
use Modules\Shared\Infrastructure\Jobs\TenantAwareJob;

class ProcessTenantDunningJob extends TenantAwareJob
{
    public function handle(): void
    {
        // For demonstration of Dunning and Retry orchestration
        // We find failed payments that haven't reached max retries
        // The TenantScope applies automatically thanks to TenantAwareJob's middleware.
        $failedPayments = Payment::where('status', 'failed')
            ->whereHas('attempts', function ($query) {
                $query->havingRaw('COUNT(id) < ?', [3]);
            })
            ->withCount('attempts')
            ->get();

        $paymentIds = $failedPayments->pluck('id')->toArray();
        if (! empty($paymentIds)) {
            Payment::whereIn('id', $paymentIds)->update(['status' => 'pending']);
        }
    }
}
