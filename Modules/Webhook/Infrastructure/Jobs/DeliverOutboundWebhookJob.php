<?php

namespace Modules\Webhook\Infrastructure\Jobs;

use Illuminate\Support\Facades\Http;
use Modules\Shared\Domain\Contracts\GetTenantSettings;
use Modules\Shared\Infrastructure\Jobs\TenantAwareJob;
use Modules\Webhook\Domain\Models\OutboundWebhookDelivery;
use Throwable;

class DeliverOutboundWebhookJob extends TenantAwareJob
{
    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        string $tenantId,
        public readonly string $eventName,
        public readonly array $payload,
        public readonly ?string $deliveryId = null
    ) {
        parent::__construct($tenantId);
    }

    public function handle(GetTenantSettings $getTenantSettings): void
    {
        $settings = $getTenantSettings->execute($this->tenantId);
        $webhookUrl = $settings['webhook_url'] ?? null;
        $webhookSecret = $settings['webhook_secret'] ?? null;

        if (! $webhookUrl) {
            return;
        }

        $delivery = null;
        if ($this->deliveryId) {
            $delivery = OutboundWebhookDelivery::find($this->deliveryId);
        }

        if (! $delivery) {
            $delivery = OutboundWebhookDelivery::create([
                'tenant_id' => $this->tenantId,
                'event_name' => $this->eventName,
                'payload' => $this->payload,
                'attempts' => 0,
            ]);
        }

        $delivery->increment('attempts');

        $jsonPayload = json_encode($this->payload) ?: '';
        $secret = is_string($webhookSecret) ? $webhookSecret : '';
        $signature = hash_hmac('sha256', $jsonPayload, $secret);

        $url = is_string($webhookUrl) ? $webhookUrl : '';
        $response = Http::withHeaders([
            'X-OmniBill-Signature' => $signature,
        ])->post($url, $this->payload);

        if ($response->successful()) {
            $delivery->update([
                'response_status' => $response->status(),
                'delivered_at' => now(),
                'error_message' => null,
            ]);
        } else {
            $delivery->update([
                'response_status' => $response->status(),
                'error_message' => $response->body(),
            ]);

            $response->throw();
        }
    }

    public function failed(Throwable $exception): void
    {
        if ($this->deliveryId) {
            $delivery = OutboundWebhookDelivery::find($this->deliveryId);
            if ($delivery) {
                $delivery->update([
                    'error_message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
