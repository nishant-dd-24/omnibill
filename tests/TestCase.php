<?php

namespace Tests;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Event;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Price;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Infrastructure\Database\PostgresRlsManager;

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
    protected function setUp(): void
    {
        parent::setUp();

        try {
            $rlsManager = app(PostgresRlsManager::class);
            $rlsManager->bypassRls();

            Event::listen(RequestHandled::class, function () use ($rlsManager) {
                $rlsManager->bypassRls();
            });
            Event::listen(JobProcessed::class, function () use ($rlsManager) {
                $rlsManager->bypassRls();
            });
            Event::listen(JobFailed::class, function () use ($rlsManager) {
                $rlsManager->bypassRls();
            });
        } catch (\Throwable $e) {
            // Ignore if class does not exist or fails to resolve
        }
    }

    protected function tearDown(): void
    {
        Context::flush();
        parent::tearDown();
    }
}
