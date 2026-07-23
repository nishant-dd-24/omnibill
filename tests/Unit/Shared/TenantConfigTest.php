<?php

namespace Tests\Unit\Shared;

use Illuminate\Support\Str;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Shared\Domain\Context\TenantConfig;
use Modules\Tenant\Infrastructure\Fake\InMemoryTenantSettings;

test('tenant config lazy loads settings and supports dot notation', function () {
    $tenantId = (string) Str::uuid();

    $currentTenant = new CurrentTenant($tenantId);

    $fakeSettings = new InMemoryTenantSettings;
    $fakeSettings->setForTenant($tenantId, [
        'billing' => [
            'currency' => 'USD',
            'tax_rate' => 10.5,
        ],
        'features' => [
            'beta_dashboard' => true,
        ],
    ]);

    $tenantConfig = new TenantConfig($currentTenant, $fakeSettings);

    expect($tenantConfig->get('billing.currency'))->toBe('USD')
        ->and($tenantConfig->get('billing.tax_rate'))->toBe(10.5)
        ->and($tenantConfig->get('features.beta_dashboard'))->toBeTrue()
        ->and($tenantConfig->get('non_existent', 'default'))->toBe('default')
        ->and($tenantConfig->has('billing.currency'))->toBeTrue()
        ->and($tenantConfig->has('non_existent'))->toBeFalse()
        ->and($tenantConfig->all())->toHaveKey('billing');
});

test('tenant config returns default when no tenant is set', function () {
    $currentTenant = new CurrentTenant;
    $fakeSettings = new InMemoryTenantSettings;

    $tenantConfig = new TenantConfig($currentTenant, $fakeSettings);

    expect($tenantConfig->get('billing.currency', 'EUR'))->toBe('EUR')
        ->and($tenantConfig->has('billing.currency'))->toBeFalse()
        ->and($tenantConfig->all())->toBe([]);
});
