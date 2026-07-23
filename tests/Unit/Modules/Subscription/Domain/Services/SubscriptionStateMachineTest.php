<?php

use Modules\Shared\Domain\Exceptions\DomainException;
use Modules\Subscription\Domain\Models\Subscription;
use Modules\Subscription\Domain\Services\SubscriptionStateMachine;
use Tests\TestCase;

uses(TestCase::class);

it('activates a pending subscription', function () {
    $subscription = new Subscription(['status' => 'pending']);
    $machine = new SubscriptionStateMachine;

    $machine->activate($subscription);

    expect($subscription->status)->toBe('active');
});

it('throws exception when activating canceled subscription', function () {
    $subscription = new Subscription(['status' => 'canceled']);
    $machine = new SubscriptionStateMachine;

    $machine->activate($subscription);
})->throws(DomainException::class);

it('marks active subscription as past due', function () {
    $subscription = new Subscription(['status' => 'active']);
    $machine = new SubscriptionStateMachine;

    $machine->markPastDue($subscription);

    expect($subscription->status)->toBe('past_due');
});

it('cancels an active subscription', function () {
    $subscription = new Subscription(['status' => 'active']);
    $machine = new SubscriptionStateMachine;

    $machine->cancel($subscription);

    expect($subscription->status)->toBe('canceled');
    expect($subscription->canceled_at)->not->toBeNull();
    expect($subscription->ended_at)->not->toBeNull();
});
