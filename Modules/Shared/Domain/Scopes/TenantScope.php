<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Modules\Shared\Domain\Context\CurrentTenant;

/**
 * @implements Scope<Model>
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $currentTenant = app(CurrentTenant::class);

        if ($currentTenant->hasTenant()) {
            $builder->where($model->getTable().'.tenant_id', $currentTenant->id());
        } else {
            // Fail closed: if a tenant-scoped model is queried without an active tenant context, return nothing.
            // Explicit platform operations must use withoutGlobalScope(TenantScope::class).
            $builder->whereRaw('1 = 0');
        }
    }
}
