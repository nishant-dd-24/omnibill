<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Services;

use InvalidArgumentException;
use Modules\Payment\Domain\Models\Payment;

class PaymentStateMachine
{
    /**
     * @var array<string, string[]>
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['processing', 'succeeded', 'failed'],
        'processing' => ['succeeded', 'failed'],
        'succeeded' => ['refunded'],
        'failed' => [],
        'refunded' => [],
    ];

    public function transitionTo(Payment $payment, string $newState): void
    {
        $currentState = $payment->status;

        if ($currentState === $newState) {
            return; // Already in this state
        }

        if (! in_array($newState, self::ALLOWED_TRANSITIONS[$currentState] ?? [], true)) {
            throw new InvalidArgumentException("Cannot transition payment from {$currentState} to {$newState}");
        }

        $payment->status = $newState;

        if ($newState === 'succeeded') {
            $payment->completed_at = now();
        }
    }
}
