<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSorting
{
    /**
     * Scope a query to sort results.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        if (! $sort) {
            return $query;
        }

        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $field = ltrim($sort, '-');

        return $query->orderBy($field, $direction);
    }
}
