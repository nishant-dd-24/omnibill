<?php

declare(strict_types=1);

namespace Modules\Subscription\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Subscription\Domain\Models\Subscription;

class SubscriptionPastDue
{
    use Dispatchable;

    public function __construct(
        public readonly Subscription $subscription
    ) {}
}
