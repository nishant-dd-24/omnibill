<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Subscription\Application\Services\CatalogService;
use Modules\Subscription\Application\Services\FeatureFlagService;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Models\TenantPlanAssignment;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Cache::clear();
});

it('checks if tenant has feature', function () {
    $plan = new Plan([
        'features' => ['advanced_reporting' => true, 'max_users' => 5],
        'is_active' => true,
    ]);
    $plan->id = 'plan-123';

    // Fake the cache for CatalogService
    Cache::put('catalog:plans', Collection::make([$plan]));

    $assignment = new TenantPlanAssignment(['plan_id' => 'plan-123']);

    $tenant = new Tenant;
    $tenant->id = 'tenant-123';
    $tenant->setRelation('planAssignment', $assignment);

    $catalogService = new CatalogService;
    $service = new FeatureFlagService($catalogService);

    expect($service->hasFeature($tenant, 'advanced_reporting'))->toBeTrue();
    expect($service->hasFeature($tenant, 'missing_feature'))->toBeFalse();
    expect($service->getFeatureValue($tenant, 'max_users'))->toBe(5);
});
