<?php

declare(strict_types=1);

namespace Modules\IdentityAccess\Domain\Enums;

enum Permission: string
{
    /**
     * Can perform cross-tenant system administration actions.
     */
    case MANAGE_SYSTEM = 'manage_system';

    /**
     * Can add, remove, and alter roles for users within a tenant.
     */
    case MANAGE_USERS = 'manage_users';

    /**
     * Can alter subscriptions, plans, and payment methods.
     */
    case MANAGE_BILLING = 'manage_billing';

    /**
     * Can view billing data and invoices.
     */
    case VIEW_INVOICES = 'view_invoices';
}
