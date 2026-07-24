<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property \Modules\Tenant\Domain\Models\Tenant $tenant
 * @property string $tenantId
 * @property \Modules\IdentityAccess\Domain\Models\User $user
 * @property \Modules\Customer\Domain\Models\Customer $customer
 * @property \Modules\Subscription\Domain\Models\Plan $plan
 * @property \Modules\Subscription\Domain\Models\Price $price
 */
abstract class TestCase extends BaseTestCase
{
    //
}
