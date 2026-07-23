<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Shared\Application\Services\AuditLogger;
use Modules\Shared\Domain\Models\AuditLog;
use RuntimeException;

uses(RefreshDatabase::class);

it('creates an audit log entry', function () {
    $logger = new AuditLogger;

    $log = $logger->log(
        action: 'CREATED',
        resourceType: 'Invoice',
        resourceId: (string) Str::uuid(),
        tenantId: (string) Str::uuid(),
        userId: (string) Str::uuid(),
        oldValues: null,
        newValues: ['amount' => 1000]
    );

    expect($log)->toBeInstanceOf(AuditLog::class);
    $this->assertDatabaseHas('audit_logs', [
        'id' => $log->id,
        'action' => 'CREATED',
        'resource_type' => 'Invoice',
    ]);
});

it('throws exception when trying to update an audit log', function () {
    $logger = new AuditLogger;
    $log = $logger->log('CREATED', 'Invoice', (string) Str::uuid());

    expect(fn () => $log->update(['action' => 'UPDATED']))
        ->toThrow(RuntimeException::class, 'Audit logs are immutable.');
});

it('throws exception when trying to delete an audit log', function () {
    $logger = new AuditLogger;
    $log = $logger->log('CREATED', 'Invoice', (string) Str::uuid());

    expect(fn () => $log->delete())
        ->toThrow(RuntimeException::class, 'Audit logs cannot be deleted.');
});
