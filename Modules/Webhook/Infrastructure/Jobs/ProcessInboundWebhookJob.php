<?php

declare(strict_types=1);

namespace Modules\Webhook\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Payment\Domain\Events\PaymentSucceeded;
use Modules\Payment\Domain\Models\Payment;
use Modules\Shared\Domain\Constants\QueueName;
use Modules\Webhook\Domain\Models\WebhookEvent;

class ProcessInboundWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $webhookEventId
    ) {
        $this->onQueue(QueueName::WEBHOOKS);
    }

    public function handle(): void
    {
        $webhookEvent = WebhookEvent::find($this->webhookEventId);

        if (! $webhookEvent) {
            return;
        }

        if ($webhookEvent->processed_at !== null) {
            return;
        }

        // Process event
        $payload = $webhookEvent->payload;
        $eventType = $webhookEvent->event_type;

        switch ($eventType) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($payload);
                break;
            case 'invoice.payment_failed':
                // handle failure
                break;
                // Add more cases as needed
        }

        $webhookEvent->update([
            'processed_at' => Carbon::now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handlePaymentIntentSucceeded(array $payload): void
    {
        // Extract payment intent ID
        /** @var array<string, mixed>|null $data */
        $data = $payload['data'] ?? null;
        /** @var array<string, mixed>|null $object */
        $object = $data['object'] ?? null;

        $paymentIntentId = $object['id'] ?? null;

        if (! is_string($paymentIntentId)) {
            return;
        }

        // Find payment by stripe_payment_intent_id
        // We need to disable tenant scope since this is a global job without tenant context
        $payment = Payment::withoutGlobalScopes()->where('stripe_payment_intent_id', $paymentIntentId)->first();

        if ($payment) {
            // Dispatch domain event
            // Note: Since this job is running in background without Tenant context, the domain event
            // will just carry the tenant_id. The listeners should handle it.
            event(new PaymentSucceeded(
                paymentId: $payment->id,
                tenantId: $payment->tenant_id
            ));
        }
    }
}
