<?php

declare(strict_types=1);

namespace Modules\Subscription\Domain\Services;

use Modules\Shared\Domain\Exceptions\DomainException;
use Modules\Subscription\Domain\Models\Subscription;

class SubscriptionStateMachine
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAST_DUE = 'past_due';

    public const STATUS_CANCELED = 'canceled';

    public function activate(Subscription $subscription): void
    {
        if (! in_array($subscription->status, [self::STATUS_PENDING, self::STATUS_PAST_DUE, self::STATUS_ACTIVE])) {
            throw new DomainException("Cannot activate subscription from status: {$subscription->status}");
        }

        $subscription->status = self::STATUS_ACTIVE;
    }

    public function markPastDue(Subscription $subscription): void
    {
        if ($subscription->status !== self::STATUS_ACTIVE) {
            throw new DomainException("Cannot mark past due from status: {$subscription->status}");
        }

        $subscription->status = self::STATUS_PAST_DUE;
    }

    public function cancel(Subscription $subscription): void
    {
        if ($subscription->status === self::STATUS_CANCELED) {
            throw new DomainException('Subscription is already canceled.');
        }

        $subscription->status = self::STATUS_CANCELED;
        $subscription->canceled_at = now();
        $subscription->ended_at = now();
    }
}
