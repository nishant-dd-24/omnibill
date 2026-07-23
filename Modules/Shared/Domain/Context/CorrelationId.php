<?php

namespace Modules\Shared\Domain\Context;

/**
 * Request-scoped singleton holding the correlation ID for distributed tracing and logging.
 */
class CorrelationId
{
    private ?string $id;

    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }
}
