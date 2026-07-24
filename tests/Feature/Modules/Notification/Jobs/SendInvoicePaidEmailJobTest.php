<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Customer\Domain\Models\Customer;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Notification\Domain\Models\NotificationLog;
use Modules\Notification\Infrastructure\Jobs\SendInvoicePaidEmailJob;
use Modules\Notification\Infrastructure\Mail\InvoicePaidMailable;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

test('it sends invoice paid email and logs success', function () {
    Mail::fake();

    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
    ]);

    $job = new SendInvoicePaidEmailJob($tenant->id, $invoice->id);
    app()->instance(CurrentTenant::class, new CurrentTenant($tenant->id));
    $job->handle();

    Mail::assertSent(InvoicePaidMailable::class, function ($mail) use ($customer, $invoice) {
        return $mail->hasTo($customer->email) &&
               $mail->invoice->id === $invoice->id;
    });

    $log = NotificationLog::where('tenant_id', $tenant->id)
        ->where('customer_id', $customer->id)
        ->where('type', 'invoice_paid')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe('sent')
        ->and($log->error_message)->toBeNull();
});

test('it logs failure and throws exception if email fails', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'Active']);
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
    ]);

    Mail::shouldReceive('to')->andThrow(new Exception('Mail server down'));

    $job = new SendInvoicePaidEmailJob($tenant->id, $invoice->id);
    app()->instance(CurrentTenant::class, new CurrentTenant($tenant->id));

    expect(fn () => $job->handle())->toThrow(Exception::class, 'Mail server down');

    $log = NotificationLog::where('tenant_id', $tenant->id)
        ->where('customer_id', $customer->id)
        ->where('type', 'invoice_paid')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe('failed')
        ->and($log->error_message)->toBe('Mail server down');
});
