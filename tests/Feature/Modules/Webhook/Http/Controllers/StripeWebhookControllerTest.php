<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Webhook\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\Webhook\Domain\Models\WebhookEvent;
use Modules\Webhook\Infrastructure\Jobs\ProcessInboundWebhookJob;
use Tests\TestCase;

class StripeWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);
    }

    public function test_it_handles_valid_webhook_and_dispatches_job(): void
    {
        Queue::fake();

        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                ],
            ],
        ]);

        $timestamp = time();
        $secret = config('services.stripe.webhook_secret');
        // generate signature
        // The signature header format is: t=...,v1=...
        $signedPayload = $timestamp.'.'.$payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);
        $sigHeader = "t={$timestamp},v1={$signature}";

        $response = $this->call('POST', '/api/v1/webhooks/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => $sigHeader,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('webhook_events', [
            'provider' => 'stripe',
            'provider_event_id' => 'evt_test_123',
            'event_type' => 'payment_intent.succeeded',
        ]);

        $event = WebhookEvent::where('provider_event_id', 'evt_test_123')->first();

        Queue::assertPushed(ProcessInboundWebhookJob::class, function ($job) use ($event) {
            return $job->webhookEventId === $event->id;
        });
    }

    public function test_it_rejects_invalid_signature(): void
    {
        Queue::fake();

        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'payment_intent.succeeded',
        ]);

        $response = $this->call('POST', '/api/v1/webhooks/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => 'invalid_signature',
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid signature']);

        $this->assertDatabaseMissing('webhook_events', [
            'provider_event_id' => 'evt_test_123',
        ]);

        Queue::assertNothingPushed();
    }

    public function test_it_handles_duplicate_events_idempotently(): void
    {
        Queue::fake();

        WebhookEvent::create([
            'provider' => 'stripe',
            'provider_event_id' => 'evt_test_123',
            'event_type' => 'payment_intent.succeeded',
            'payload' => ['id' => 'evt_test_123'],
        ]);

        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'payment_intent.succeeded',
        ]);

        $timestamp = time();
        $secret = config('services.stripe.webhook_secret');
        $signedPayload = $timestamp.'.'.$payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);
        $sigHeader = "t={$timestamp},v1={$signature}";

        $response = $this->call('POST', '/api/v1/webhooks/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => $sigHeader,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'already processed']);

        // Should not push another job
        Queue::assertNothingPushed();
    }
}
