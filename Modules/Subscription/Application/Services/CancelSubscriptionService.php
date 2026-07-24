<?php

declare(strict_types=1);

namespace Modules\Subscription\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Subscription\Domain\Events\SubscriptionCancelled;
use Modules\Subscription\Domain\Models\Subscription;
use Modules\Subscription\Domain\Services\SubscriptionStateMachine;

class CancelSubscriptionService
{
    public function __construct(
        private readonly SubscriptionStateMachine $stateMachine
    ) {}

    public function execute(Subscription $subscription, bool $atPeriodEnd = false): Subscription
    {
        return DB::transaction(function () use ($subscription, $atPeriodEnd) {
            if ($atPeriodEnd) {
                $subscription->cancel_at_period_end = true;
                $subscription->save();
            } else {
                $this->stateMachine->cancel($subscription);
                $subscription->save();

                event(new SubscriptionCancelled($subscription));
            }

            return $subscription;
        });
    }
}
