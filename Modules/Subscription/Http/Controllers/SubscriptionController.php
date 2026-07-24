<?php

declare(strict_types=1);

namespace Modules\Subscription\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Subscription\Application\Services\CancelSubscriptionService;
use Modules\Subscription\Application\Services\CreateSubscriptionService;
use Modules\Subscription\Application\Services\UpdateSubscriptionService;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Subscription;
use Modules\Subscription\Http\Requests\StoreSubscriptionRequest;
use Modules\Subscription\Http\Requests\UpdateSubscriptionRequest;
use Modules\Subscription\Http\Resources\SubscriptionResource;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CreateSubscriptionService $createService,
        private readonly UpdateSubscriptionService $updateService,
        private readonly CancelSubscriptionService $cancelService,
        private readonly CurrentTenant $currentTenant
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var Builder<Subscription> $query */
        $query = Subscription::query()->with('items');

        return SubscriptionResource::collection($query->cursorPaginate());
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $tenantId = $this->currentTenant->id();
        if (! $tenantId) {
            throw new \RuntimeException('No tenant context found.');
        }

        /** @var string $planId */
        $planId = $request->validated('plan_id');

        /** @var Plan $plan */
        $plan = Plan::findOrFail($planId);

        /** @var array<int, array{price_id: string, quantity?: int}> $items */
        $items = $request->validated('items');

        /** @var string $customerId */
        $customerId = $request->validated('customer_id');

        $subscription = $this->createService->execute(
            $tenantId,
            $customerId,
            $plan,
            $items
        );

        $subscription->load('items');

        return (new SubscriptionResource($subscription))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Subscription $subscription): SubscriptionResource
    {
        $subscription->loadMissing('items');

        return new SubscriptionResource($subscription);
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): SubscriptionResource
    {
        /** @var string $planId */
        $planId = $request->validated('plan_id');

        /** @var Plan $plan */
        $plan = Plan::findOrFail($planId);

        /** @var array<int, array{price_id: string, quantity?: int}> $items */
        $items = $request->validated('items');

        $updatedSubscription = $this->updateService->execute(
            $subscription,
            $plan,
            $items
        );

        $updatedSubscription->load('items');

        return new SubscriptionResource($updatedSubscription);
    }

    public function destroy(Request $request, Subscription $subscription): SubscriptionResource
    {
        /** @var bool $atPeriodEnd */
        $atPeriodEnd = filter_var($request->query('at_period_end', false), FILTER_VALIDATE_BOOLEAN);

        $cancelledSubscription = $this->cancelService->execute(
            $subscription,
            $atPeriodEnd
        );

        $cancelledSubscription->load('items');

        return new SubscriptionResource($cancelledSubscription);
    }
}
