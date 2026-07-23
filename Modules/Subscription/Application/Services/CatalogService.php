<?php

declare(strict_types=1);

namespace Modules\Subscription\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Subscription\Domain\Models\Plan;

class CatalogService
{
    private const CACHE_KEY = 'catalog:plans';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * @return Collection<int, Plan>
     */
    public function getActivePlans(): Collection
    {
        /** @var Collection<int, Plan> $plans */
        $plans = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Plan::where('is_active', true)->with('prices')->get();
        });

        return $plans;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function getPlanById(string $planId): ?Plan
    {
        return $this->getActivePlans()->firstWhere('id', $planId);
    }
}
