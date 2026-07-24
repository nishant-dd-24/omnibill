<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Shared\Domain\Scopes\TenantScope;

trait TenantScoped
{
    /**
     * Boot the trait to apply the global scope and automatically assign tenant_id.
     */
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $currentTenant = app(CurrentTenant::class);

            if ($currentTenant->hasTenant() && ! $model->getAttribute('tenant_id')) {
                $model->setAttribute('tenant_id', $currentTenant->id());
            }
        });
    }
}
