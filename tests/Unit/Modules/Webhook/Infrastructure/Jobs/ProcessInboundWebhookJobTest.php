<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Webhook\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Customer\Domain\Models\Customer;
use Modules\Payment\Domain\Events\PaymentSucceeded;
use Modules\Payment\Domain\Models\Payment;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Webhook\Domain\Models\WebhookEvent;
use Modules\Webhook\Infrastructure\Jobs\ProcessInboundWebhookJob;
use Tests\TestCase;

class ProcessInboundWebhookJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
    }

    public function test_it_processes_payment_intent_succeeded(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'status' => 'active',
        ]);

        // Create a Payment without global scopes since it uses TenantScoped
        $payment = Payment::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'invoice_id' => null,
            'customer_id' => $customer->id,
            'payment_method_id' => null,
            'stripe_payment_intent_id' => 'pi_test_123',
            'amount' => 1000,
            'currency' => 'usd',
            'status' => 'pending',
        ]);

        $webhookEvent = WebhookEvent::create([
            'provider' => 'stripe',
            'provider_event_id' => 'evt_test_123',
            'event_type' => 'payment_intent.succeeded',
            'payload' => [
                'data' => [
                    'object' => [
                        'id' => 'pi_test_123',
                    ],
                ],
            ],
        ]);

        $job = new ProcessInboundWebhookJob($webhookEvent->id);
        $job->handle();

        $webhookEvent->refresh();
        $this->assertNotNull($webhookEvent->processed_at);

        Event::assertDispatched(PaymentSucceeded::class, function ($event) use ($payment, $tenant) {
            return $event->paymentId === $payment->id && $event->tenantId === $tenant->id;
        });
    }

    public function test_it_does_not_process_already_processed_event(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $webhookEvent = WebhookEvent::create([
            'provider' => 'stripe',
            'provider_event_id' => 'evt_test_123',
            'event_type' => 'payment_intent.succeeded',
            'payload' => ['data' => ['object' => ['id' => 'pi_test_123']]],
            'processed_at' => Carbon::now(),
        ]);

        $job = new ProcessInboundWebhookJob($webhookEvent->id);
        $job->handle();

        Event::assertNotDispatched(PaymentSucceeded::class);
    }
}
