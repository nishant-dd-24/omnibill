<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface OmniBillUser extends Authenticatable
{
    public function hasRole(string $role): bool;

    public function getTenantId(): ?string;
}
