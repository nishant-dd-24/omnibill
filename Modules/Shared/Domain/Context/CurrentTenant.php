<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Context;

/**
 * Request-scoped singleton holding the resolved tenant context.
 */
class CurrentTenant
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

    public function hasTenant(): bool
    {
        return $this->id !== null;
    }
}
