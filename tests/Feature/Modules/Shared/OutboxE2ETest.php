<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Modules\Shared\Domain\Models\OutboxEvent;
use Modules\Shared\Infrastructure\Console\DispatchOutboxEventsCommand;
use Modules\Shared\Infrastructure\Jobs\ProcessOutboxEventJob;

uses(RefreshDatabase::class);

it('processes outbox events successfully', function () {
    Queue::fake();

    $tenantId = Str::uuid()->toString();

    DB::transaction(function () use ($tenantId) {
        OutboxEvent::create([
            'event_name' => 'TestDomainEvent',
            'payload' => ['foo' => 'bar'],
            'tenant_id' => $tenantId,
            'correlation_id' => Str::uuid()->toString(),
        ]);
    });

    Artisan::registerCommand(app(DispatchOutboxEventsCommand::class));
    $this->artisan('omnibill:dispatch-outbox')->assertExitCode(0);

    Queue::assertPushed(ProcessOutboxEventJob::class, function ($job) use ($tenantId) {
        return $job->eventName === 'TestDomainEvent' &&
               $job->payload['foo'] === 'bar' &&
               $job->tenantId === $tenantId;
    });

    $this->assertDatabaseHas('outbox_events', [
        'event_name' => 'TestDomainEvent',
    ]);

    $outboxEvent = OutboxEvent::first();
    expect($outboxEvent->processed_at)->not->toBeNull();
});

it('fires event when job is executed', function () {
    Event::fake();

    $tenantId = Str::uuid()->toString();

    $job = new ProcessOutboxEventJob($tenantId, 'TestDomainEvent', ['foo' => 'bar']);

    $job->handle();

    Event::assertDispatched('TestDomainEvent', function ($event, $payload) {
        return $payload === ['foo' => 'bar'];
    });
});
