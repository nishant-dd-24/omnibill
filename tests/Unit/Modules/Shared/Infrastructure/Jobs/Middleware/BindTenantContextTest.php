<?php

declare(strict_types=1);

use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Shared\Infrastructure\Jobs\Middleware\BindTenantContext;
use Tests\TestCase;

uses(TestCase::class);

it('binds the tenant context if tenantId is present on the job', function () {
    $job = new class
    {
        public string $tenantId = 'test-tenant-123';
    };

    $middleware = new BindTenantContext;

    $called = false;
    $boundTenantId = null;
    $middleware->handle($job, function () use (&$called, &$boundTenantId) {
        $called = true;
        $boundTenantId = app(CurrentTenant::class)->id();
    });

    expect($called)->toBeTrue()
        ->and($boundTenantId)->toBe('test-tenant-123');
});

it('does not crash if tenantId is missing', function () {
    $job = new class {};

    $middleware = new BindTenantContext;

    $called = false;
    $middleware->handle($job, function () use (&$called) {
        $called = true;
    });

    expect($called)->toBeTrue();
});
