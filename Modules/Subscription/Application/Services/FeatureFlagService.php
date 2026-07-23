<?php

declare(strict_types=1);

namespace Modules\Subscription\Application\Services;

use Modules\Tenant\Domain\Models\Tenant;

class FeatureFlagService
{
    private CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function hasFeature(Tenant $tenant, string $featureKey): bool
    {
        $assignment = $tenant->planAssignment;
        if (! $assignment || ! $assignment->plan_id) {
            return false;
        }

        $plan = $this->catalogService->getPlanById($assignment->plan_id);
        if (! $plan) {
            return false;
        }

        $features = $plan->features ?? [];

        return isset($features[$featureKey]) && $features[$featureKey] !== false;
    }

    public function getFeatureValue(Tenant $tenant, string $featureKey): mixed
    {
        $assignment = $tenant->planAssignment;
        if (! $assignment || ! $assignment->plan_id) {
            return null;
        }

        $plan = $this->catalogService->getPlanById($assignment->plan_id);
        if (! $plan) {
            return null;
        }

        $features = $plan->features ?? [];

        return $features[$featureKey] ?? null;
    }
}
