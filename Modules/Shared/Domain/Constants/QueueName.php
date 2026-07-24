<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Constants;

class QueueName
{
    public const BILLING_CRITICAL = 'billing-critical';

    public const INVOICING = 'invoicing';

    public const NOTIFICATIONS = 'notifications';

    public const WEBHOOKS = 'webhooks';

    public const DEFAULT = 'default';
}
