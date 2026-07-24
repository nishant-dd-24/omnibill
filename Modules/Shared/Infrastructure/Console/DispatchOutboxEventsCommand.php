<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Shared\Domain\Constants\QueueName;
use Modules\Shared\Domain\Models\OutboxEvent;
use Modules\Shared\Infrastructure\Jobs\ProcessOutboxEventJob;

class DispatchOutboxEventsCommand extends Command
{
    protected $signature = 'omnibill:dispatch-outbox {--limit=100 : Number of events to process}';

    protected $description = 'Polls the outbox_events table and dispatches events to the appropriate queues';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Polling outbox events with limit {$limit}...");

        DB::transaction(function () use ($limit) {
            $events = OutboxEvent::whereNull('processed_at')
                ->orderBy('created_at', 'asc')
                ->limit($limit)
                ->lockForUpdate()
                ->get();

            if ($events->isEmpty()) {
                $this->info('No pending outbox events found.');

                return self::SUCCESS;
            }

            $this->info("Found {$events->count()} pending outbox event(s). Dispatching...");

            /** @var OutboxEvent $event */
            foreach ($events as $event) {
                try {
                    $this->dispatchEvent($event);

                    $event->update([
                        'processed_at' => now(),
                    ]);
                } catch (Exception $e) {
                    Log::error("Failed to dispatch outbox event {$event->id}: ".$e->getMessage(), [
                        'event_id' => $event->id,
                        'exception' => $e,
                    ]);
                }
            }

            $this->info('Outbox events dispatched successfully.');
        });

        return self::SUCCESS;
    }

    private function dispatchEvent(OutboxEvent $event): void
    {
        $queue = $event->queue ?? QueueName::DEFAULT;

        /** @var array<string, mixed> $payload */
        $payload = $event->payload;

        ProcessOutboxEventJob::dispatch(
            $event->tenant_id ?? '',
            $event->event_name,
            $payload
        )->onQueue($queue);
    }
}
