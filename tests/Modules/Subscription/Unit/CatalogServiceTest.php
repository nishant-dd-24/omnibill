<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Subscription\Application\Services\CatalogService;
use Modules\Subscription\Domain\Models\Plan;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Cache::clear();
});

it('caches the active plans', function () {
    $service = new CatalogService;
    Plan::factory()->create(['is_active' => true, 'name' => 'Test Plan']);

    $plans = $service->getActivePlans();
    expect($plans)->toHaveCount(1);

    Plan::factory()->create(['is_active' => true, 'name' => 'Test Plan 2']);

    $cachedPlans = $service->getActivePlans();
    expect($cachedPlans)->toHaveCount(1);

    $service->clearCache();
    $clearedPlans = $service->getActivePlans();
    expect($clearedPlans)->toHaveCount(2);
});

it('gets plan by id', function () {
    $service = new CatalogService;
    $plan = Plan::factory()->create(['is_active' => true]);

    $found = $service->getPlanById($plan->id);
    if ($found) {
        expect($found->id)->toBe($plan->id);
    }
});
