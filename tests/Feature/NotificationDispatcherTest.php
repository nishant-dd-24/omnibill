<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Notification\Application\Services\NotificationDispatcher;
use Modules\Notification\Domain\Models\NotificationLog;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->subject('Test Subject')->html('Test Body');
    }
}

it('dispatches email and logs it successfully', function () {
    Mail::fake();

    $dispatcher = new NotificationDispatcher;
    $tenantId = (string) Str::uuid();
    $recipient = 'test@example.com';
    $mailable = new TestMailable;

    // Disable tenant scope or fake tenant resolution because the model uses TenantScoped
    Tenant::forceCreate(['id' => $tenantId, 'name' => 'Test Tenant', 'status' => 'active']);
    app()->instance('tenant_id', $tenantId); // Depends on how TenantScope resolves it, wait. Let's just bypass it for now.

    NotificationLog::withoutEvents(function () use ($dispatcher, $tenantId, $recipient, $mailable) {
        $dispatcher->dispatchEmail($tenantId, $recipient, $mailable);
    });

    Mail::assertSent(TestMailable::class, function ($mail) use ($recipient) {
        return $mail->hasTo($recipient);
    });

    $this->assertDatabaseHas('notification_logs', [
        'tenant_id' => $tenantId,
        'type' => 'email',
        'recipient' => $recipient,
        'subject' => 'TestMailable',
        'status' => 'sent',
    ]);
});

it('logs failure if email dispatch fails', function () {
    $dispatcher = new NotificationDispatcher();
    $tenantId = (string) Str::uuid();
    $recipient = 'test@example.com';
    $mailable = new TestMailable();

    Tenant::forceCreate(['id' => $tenantId, 'name' => 'Test Tenant', 'status' => 'active']);
    app()->instance('tenant_id', $tenantId);

    Mail::shouldReceive('to')->andThrow(new Exception('Mail failed'));

    expect(fn() => NotificationLog::withoutEvents(fn() => $dispatcher->dispatchEmail($tenantId, $recipient, $mailable)))
        ->toThrow(Exception::class, 'Mail failed');

    $this->assertDatabaseHas('notification_logs', [
        'tenant_id' => $tenantId,
        'type' => 'email',
        'recipient' => $recipient,
        'status' => 'failed',
        'error_message' => 'Mail failed',
    ]);
});
