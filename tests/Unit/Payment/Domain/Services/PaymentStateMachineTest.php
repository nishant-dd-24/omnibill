<?php

declare(strict_types=1);

namespace Tests\Unit\Payment\Domain\Services;

use InvalidArgumentException;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Domain\Services\PaymentStateMachine;
use Tests\TestCase;

class PaymentStateMachineTest extends TestCase
{
    private PaymentStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new PaymentStateMachine;
    }

    public function test_can_transition_from_pending_to_processing(): void
    {
        $payment = new Payment(['status' => 'pending']);
        $this->stateMachine->transitionTo($payment, 'processing');
        $this->assertEquals('processing', $payment->status);
    }

    public function test_can_transition_from_processing_to_succeeded(): void
    {
        $payment = new Payment(['status' => 'processing']);
        $this->stateMachine->transitionTo($payment, 'succeeded');
        $this->assertEquals('succeeded', $payment->status);
        $this->assertNotNull($payment->completed_at);
    }

    public function test_cannot_transition_from_succeeded_to_failed(): void
    {
        $payment = new Payment(['status' => 'succeeded']);
        $this->expectException(InvalidArgumentException::class);
        $this->stateMachine->transitionTo($payment, 'failed');
    }
}
