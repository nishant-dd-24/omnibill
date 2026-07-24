<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Jobs;

class ProcessOutboxEventJob extends TenantAwareJob
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        string $tenantId,
        public readonly string $eventName,
        public readonly array $payload
    ) {
        parent::__construct($tenantId);
    }

    public function handle(): void
    {
        if (class_exists($this->eventName)) {
            // Instantiate the event dynamically.
            // Assuming payload can be passed to the constructor.
            $event = app()->makeWith($this->eventName, $this->payload);
            event($event);
        } else {
            event($this->eventName, $this->payload);
        }
    }
}
