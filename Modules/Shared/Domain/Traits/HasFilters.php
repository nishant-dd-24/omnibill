<?php

namespace Modules\Shared\Domain\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasFilters
{
    /**
     * Scope a query to only include filtered results.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        return $query;
    }
}
