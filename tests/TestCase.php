<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Price;
use Modules\Tenant\Domain\Models\Tenant;

/**
 * @property Tenant $tenant
 * @property string $tenantId
 * @property User $user
 * @property Customer $customer
 * @property Plan $plan
 * @property Price $price
 */
abstract class TestCase extends BaseTestCase
{
    //
}
