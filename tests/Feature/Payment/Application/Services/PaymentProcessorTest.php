<?php

declare(strict_types=1);

namespace Tests\Feature\Payment\Application\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Customer\Domain\Models\Customer;
use Modules\Payment\Application\Services\PaymentProcessor;
use Modules\Payment\Domain\Events\PaymentFailed;
use Modules\Payment\Domain\Events\PaymentSucceeded;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Domain\Services\PaymentStateMachine;
use Modules\Tenant\Domain\Models\Tenant;
use Tests\TestCase;

class PaymentProcessorTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Customer $customer;

    private Payment $payment;

    private PaymentProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
        $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'amount' => 1000,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $this->processor = new PaymentProcessor(new PaymentStateMachine);
    }

    public function test_processes_successful_webhook_creates_attempt_and_dispatches_event(): void
    {
        Event::fake();

        $this->processor->processWebhook($this->payment, 'succeeded', ['id' => 'ch_123', 'status' => 'succeeded']);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'succeeded',
        ]);

        $this->assertDatabaseHas('payment_attempts', [
            'payment_id' => $this->payment->id,
            'status' => 'succeeded',
            'attempt_number' => 1,
        ]);

        Event::assertDispatched(PaymentSucceeded::class, function (PaymentSucceeded $event) {
            return $event->paymentId === $this->payment->id && $event->tenantId === $this->tenant->id;
        });
    }

    public function test_processes_failed_webhook_creates_attempt_and_dispatches_event(): void
    {
        Event::fake();

        $this->processor->processWebhook($this->payment, 'failed', ['error' => 'card_declined']);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('payment_attempts', [
            'payment_id' => $this->payment->id,
            'status' => 'failed',
            'attempt_number' => 1,
        ]);

        Event::assertDispatched(PaymentFailed::class, function (PaymentFailed $event) {
            return $event->paymentId === $this->payment->id && $event->errorReason === 'card_declined';
        });
    }
}
