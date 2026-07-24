<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Context;
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
    protected function setUp(): void
    {
        parent::setUp();
        
        try {
            $rlsManager = app(\Modules\Tenant\Infrastructure\Database\PostgresRlsManager::class);
            $rlsManager->bypassRls();
            
            \Illuminate\Support\Facades\Event::listen(\Illuminate\Foundation\Http\Events\RequestHandled::class, function () use ($rlsManager) {
                $rlsManager->bypassRls();
            });
            \Illuminate\Support\Facades\Event::listen(\Illuminate\Queue\Events\JobProcessed::class, function () use ($rlsManager) {
                $rlsManager->bypassRls();
            });
            \Illuminate\Support\Facades\Event::listen(\Illuminate\Queue\Events\JobFailed::class, function () use ($rlsManager) {
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
