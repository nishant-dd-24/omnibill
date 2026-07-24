<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Domain\Models\Customer;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Payment\Application\Services\InitiatePaymentService;
use Modules\Payment\Domain\Models\Payment;
use Modules\Subscription\Application\Adapters\BillingGatewayInterface;
use Modules\Tenant\Domain\Models\Tenant;

use Modules\IdentityAccess\Domain\Models\User;

use Modules\Shared\Domain\Context\CurrentTenant;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $this->app->instance(CurrentTenant::class, new CurrentTenant($this->tenant->id));
    $this->user = clone User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

    $this->customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stripe_id' => 'cus_123',
    ]);
});

it('initiates payment and returns client secret', function () {
    $invoice = Invoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'status' => 'open',
        'amount_due' => 1000,
        'currency' => 'usd',
    ]);

    $mockGateway = $this->mock(BillingGatewayInterface::class, function ($mock) use ($invoice) {
        $mock->shouldReceive('createPaymentIntent')
            ->once()
            ->with(1000, 'usd', 'cus_123', [
                'invoice_id' => $invoice->id,
                'tenant_id' => $this->tenant->id,
            ])
            ->andReturn('pi_abc123_secret_xyz789');
    });

    $service = new InitiatePaymentService($mockGateway);
    $clientSecret = $service->execute($invoice->id);

    expect($clientSecret)->toBe('pi_abc123_secret_xyz789');

    // Assert Payment created
    $this->assertDatabaseHas('payments', [
        'tenant_id' => $this->tenant->id,
        'invoice_id' => $invoice->id,
        'stripe_payment_intent_id' => 'pi_abc123',
        'status' => 'pending',
    ]);

    $payment = Payment::where('invoice_id', $invoice->id)->first();

    // Assert PaymentAttempt created
    $this->assertDatabaseHas('payment_attempts', [
        'tenant_id' => $this->tenant->id,
        'payment_id' => $payment->id,
        'attempt_number' => 1,
        'status' => 'pending',
    ]);
});

it('creates stripe customer if stripe_id is null', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stripe_id' => null,
    ]);

    $invoice = Invoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $customer->id,
        'status' => 'open',
        'amount_due' => 2000,
        'currency' => 'eur',
    ]);

    $mockGateway = $this->mock(BillingGatewayInterface::class, function ($mock) use ($customer, $invoice) {
        $mock->shouldReceive('createCustomer')
            ->once()
            ->with(
                $customer->email,
                $customer->name,
                ['tenant_id' => $this->tenant->id]
            )
            ->andReturn('cus_new456');

        $mock->shouldReceive('createPaymentIntent')
            ->once()
            ->with(2000, 'eur', 'cus_new456', [
                'invoice_id' => $invoice->id,
                'tenant_id' => $this->tenant->id,
            ])
            ->andReturn('pi_def456_secret_wxy123');
    });

    $service = new InitiatePaymentService($mockGateway);
    $clientSecret = $service->execute($invoice->id);

    expect($clientSecret)->toBe('pi_def456_secret_wxy123');

    // check customer was updated
    expect($customer->fresh()->stripe_id)->toBe('cus_new456');
});

it('throws exception if invoice is not open', function () {
    $invoice = Invoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'status' => 'draft',
    ]);

    $service = new InitiatePaymentService($this->mock(BillingGatewayInterface::class));

    expect(fn () => $service->execute($invoice->id))->toThrow(InvalidArgumentException::class);
});
