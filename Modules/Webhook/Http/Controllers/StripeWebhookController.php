<?php

declare(strict_types=1);

namespace Modules\Webhook\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Webhook\Domain\Models\WebhookEvent;
use Modules\Webhook\Infrastructure\Jobs\ProcessInboundWebhookJob;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        if (! is_string($sigHeader)) {
            return response()->json(['error' => 'Invalid signature header'], 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Idempotency check
        $webhookEvent = WebhookEvent::where('provider', 'stripe')
            ->where('provider_event_id', $event->id)
            ->first();

        if ($webhookEvent) {
            return response()->json(['status' => 'already processed']);
        }

        // Persist to webhook_events
        $webhookEvent = WebhookEvent::create([
            'provider' => 'stripe',
            'provider_event_id' => $event->id,
            'event_type' => $event->type,
            'payload' => $request->all(),
        ]);

        // Dispatch job
        ProcessInboundWebhookJob::dispatch($webhookEvent->id);

        return response()->json(['status' => 'success']);
    }
}
