<?php

namespace Modules\IdentityAccess\Domain\Enums;

enum Role: string
{
    /**
     * Platform operator. Cross-tenant capability via explicit bypass path.
     */
    case SUPER_ADMIN = 'super_admin';

    /**
     * Tenant owner/admin. Can manage users and billing for their tenant.
     */
    case TENANT_ADMIN = 'tenant_admin';

    /**
     * Billing manager. Can view/edit invoices and payment methods for their tenant, but cannot manage users.
     */
    case TENANT_BILLING_MANAGER = 'tenant_billing_manager';

    /**
     * Standard user. Can view own resources within the tenant.
     */
    case TENANT_USER = 'tenant_user';
}
