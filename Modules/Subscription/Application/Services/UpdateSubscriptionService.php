<?php

declare(strict_types=1);

namespace Modules\Subscription\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Subscription;
use Modules\Subscription\Domain\Models\SubscriptionItem;

class UpdateSubscriptionService
{
    /**
     * @param  array<int, array{price_id: string, quantity?: int}>  $newItemsData
     */
    public function execute(Subscription $subscription, Plan $newPlan, array $newItemsData): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlan, $newItemsData) {
            $subscription->plan_id = $newPlan->id;
            $subscription->save();

            // Replace items
            $subscription->items()->delete();

            foreach ($newItemsData as $itemData) {
                SubscriptionItem::create([
                    'subscription_id' => $subscription->id,
                    'price_id' => $itemData['price_id'],
                    'quantity' => $itemData['quantity'] ?? 1,
                ]);
            }

            return $subscription;
        });
    }
}
