<?php

declare(strict_types=1);

namespace Modules\Subscription\Infrastructure\Jobs;

use Illuminate\Support\Facades\Log;
use Modules\Shared\Infrastructure\Jobs\TenantAwareJob;
use Modules\Subscription\Domain\Models\Subscription;
use Modules\Subscription\Domain\Services\SubscriptionStateMachine;

class EvaluateSubscriptionRenewalJob extends TenantAwareJob
{
    public function __construct(
        string $tenantId,
        public readonly string $subscriptionId
    ) {
        parent::__construct($tenantId);
    }

    public function handle(SubscriptionStateMachine $stateMachine): void
    {
        $subscription = Subscription::find($this->subscriptionId);

        if (! $subscription) {
            return;
        }

        if ($subscription->cancel_at_period_end) {
            $stateMachine->cancel($subscription);
            $subscription->save();
            Log::info("Subscription {$subscription->id} was canceled at period end.");
        }
    }
}
