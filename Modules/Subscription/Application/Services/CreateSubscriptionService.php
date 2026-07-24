<?php

declare(strict_types=1);

namespace Modules\Subscription\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Customer\Domain\Models\Customer;
use Modules\Subscription\Domain\Events\SubscriptionActivated;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Subscription;
use Modules\Subscription\Domain\Models\SubscriptionItem;
use Modules\Subscription\Domain\Services\SubscriptionStateMachine;

class CreateSubscriptionService
{
    public function __construct(
        private readonly SubscriptionStateMachine $stateMachine
    ) {}

    /**
     * @param  array<int, array{price_id: string, quantity?: int}>  $itemsData
     */
    public function execute(string $tenantId, string $customerId, Plan $plan, array $itemsData): Subscription
    {
        $customer = Customer::where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $validPriceIds = $plan->prices()->pluck('id')->toArray();
        foreach ($itemsData as $item) {
            if (! in_array($item['price_id'], $validPriceIds)) {
                throw new \DomainException("Price {$item['price_id']} does not belong to the selected plan.");
            }
        }

        return DB::transaction(function () use ($tenantId, $customer, $plan, $itemsData) {
            $subscription = Subscription::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStateMachine::STATUS_PENDING,
            ]);

            foreach ($itemsData as $itemData) {
                SubscriptionItem::create([
                    'subscription_id' => $subscription->id,
                    'price_id' => $itemData['price_id'],
                    'quantity' => $itemData['quantity'] ?? 1,
                ]);
            }

            $this->stateMachine->activate($subscription);
            $subscription->save();

            DB::afterCommit(fn () => event(new SubscriptionActivated($subscription)));

            return $subscription;
        });
    }
}
