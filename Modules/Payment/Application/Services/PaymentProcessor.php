<?php

declare(strict_types=1);

namespace Modules\Payment\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Payment\Domain\Events\PaymentFailed;
use Modules\Payment\Domain\Events\PaymentRefunded;
use Modules\Payment\Domain\Events\PaymentSucceeded;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Domain\Services\PaymentStateMachine;

class PaymentProcessor
{
    public function __construct(
        private readonly PaymentStateMachine $stateMachine
    ) {}

    /**
     * @param  array<string, mixed>  $gatewayResponse
     */
    public function processWebhook(Payment $payment, string $status, array $gatewayResponse): void
    {
        DB::transaction(function () use ($payment, $status, $gatewayResponse) {
            $this->stateMachine->transitionTo($payment, $status);
            $payment->save();

            $attemptNumber = $payment->attempts()->count() + 1;

            $payment->attempts()->create([
                'tenant_id' => $payment->tenant_id,
                'attempt_number' => $attemptNumber,
                'status' => $status,
                'gateway_response' => $gatewayResponse,
                'attempted_at' => now(),
            ]);

            // Dispatch events based on status
            if ($status === 'succeeded') {
                DB::afterCommit(fn () => event(new PaymentSucceeded($payment->id, $payment->tenant_id)));
            } elseif ($status === 'failed') {
                DB::afterCommit(fn () => event(new PaymentFailed($payment->id, $payment->tenant_id, $gatewayResponse['error'] ?? 'Unknown error')));
            } elseif ($status === 'refunded') {
                DB::afterCommit(fn () => event(new PaymentRefunded($payment->id, $payment->tenant_id)));
            }
        });
    }
}
