<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Shared\Infrastructure\Jobs\Middleware\BindTenantContext;

uses(RefreshDatabase::class);

it('binds and restores tenant context around job execution', function () {
    $job = new class
    {
        public string $tenantId;

        public function __construct()
        {
            $this->tenantId = Str::uuid()->toString();
        }
    };

    $middleware = new BindTenantContext;

    expect(app()->bound(CurrentTenant::class))->toBeFalse();

    $executed = false;

    $middleware->handle($job, function ($passedJob) use (&$executed, $job) {
        $executed = true;
        expect($passedJob)->toBe($job);

        expect(app()->bound(CurrentTenant::class))->toBeTrue();
        $tenant = app(CurrentTenant::class);
        expect($tenant->id())->toBe($job->tenantId);
    });

    expect($executed)->toBeTrue();
    expect(app()->bound(CurrentTenant::class))->toBeFalse();
});

it('restores previous tenant context if it was already bound', function () {
    $previousTenantId = Str::uuid()->toString();
    $jobTenantId = Str::uuid()->toString();

    app()->instance(CurrentTenant::class, new CurrentTenant($previousTenantId));

    $job = new class($jobTenantId)
    {
        public function __construct(public string $tenantId) {}
    };

    $middleware = new BindTenantContext;

    $middleware->handle($job, function () use ($jobTenantId) {
        $tenant = app(CurrentTenant::class);
        expect($tenant->id())->toBe($jobTenantId);
    });

    $restoredTenant = app(CurrentTenant::class);
    expect($restoredTenant->id())->toBe($previousTenantId);
});

it('unwinds tenant context even if job throws an exception', function () {
    $job = new class
    {
        public string $tenantId;

        public function __construct()
        {
            $this->tenantId = Str::uuid()->toString();
        }
    };

    $middleware = new BindTenantContext;

    expect(app()->bound(CurrentTenant::class))->toBeFalse();

    try {
        $middleware->handle($job, function () {
            throw new Exception('Job failed');
        });
    } catch (Exception $e) {
        // Exception caught
    }

    expect(app()->bound(CurrentTenant::class))->toBeFalse();
});
