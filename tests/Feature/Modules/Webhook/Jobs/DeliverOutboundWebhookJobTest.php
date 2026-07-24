<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Modules\Shared\Domain\Contracts\GetTenantSettings;
use Modules\Tenant\Infrastructure\Fake\InMemoryTenantSettings;
use Modules\Webhook\Infrastructure\Jobs\DeliverOutboundWebhookJob;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenantSettings = new InMemoryTenantSettings;
    $this->app->instance(GetTenantSettings::class, $this->tenantSettings);
    $this->tenantId = '018f2f4c-7c01-789a-b45c-0123456789ab';
});

it('does not send webhook if url is missing', function () {
    Http::fake();

    $this->tenantSettings->setForTenant($this->tenantId, [
        'webhook_url' => null,
        'webhook_secret' => null,
    ]);

    $job = new DeliverOutboundWebhookJob($this->tenantId, 'customer.created', ['id' => 1]);
    $job->handle($this->tenantSettings);

    Http::assertNothingSent();
    $this->assertDatabaseEmpty('outbound_webhook_deliveries');
});

it('sends webhook and records delivery on success', function () {
    Http::fake([
        'https://example.com/webhook' => Http::response(['status' => 'ok'], 200),
    ]);

    $this->tenantSettings->setForTenant($this->tenantId, [
        'webhook_url' => 'https://example.com/webhook',
        'webhook_secret' => 'secret123',
    ]);

    $payload = ['id' => 123, 'name' => 'John'];
    $job = new DeliverOutboundWebhookJob($this->tenantId, 'customer.created', $payload);
    $job->handle($this->tenantSettings);

    Http::assertSent(function ($request) use ($payload) {
        $expectedSignature = hash_hmac('sha256', json_encode($payload) ?: '', 'secret123');

        return $request->url() === 'https://example.com/webhook'
            && $request->header('X-OmniBill-Signature')[0] === $expectedSignature
            && $request->data() == $payload;
    });

    $this->assertDatabaseHas('outbound_webhook_deliveries', [
        'tenant_id' => $this->tenantId,
        'event_name' => 'customer.created',
        'response_status' => 200,
        'attempts' => 1,
        'error_message' => null,
    ]);
});

it('throws exception and records error on failure', function () {
    Http::fake([
        'https://example.com/webhook' => Http::response('Internal Server Error', 500),
    ]);

    $this->tenantSettings->setForTenant($this->tenantId, [
        'webhook_url' => 'https://example.com/webhook',
        'webhook_secret' => 'secret123',
    ]);

    $payload = ['id' => 123];
    $job = new DeliverOutboundWebhookJob($this->tenantId, 'customer.created', $payload);

    $this->expectException(RequestException::class);

    try {
        $job->handle($this->tenantSettings);
    } finally {
        $this->assertDatabaseHas('outbound_webhook_deliveries', [
            'tenant_id' => $this->tenantId,
            'event_name' => 'customer.created',
            'response_status' => 500,
            'attempts' => 1,
            'error_message' => 'Internal Server Error',
        ]);
    }
});
