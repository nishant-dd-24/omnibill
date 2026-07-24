<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Domain\Traits\HasFilters;
use Modules\Shared\Domain\Traits\HasSorting;

abstract class BaseModel extends Model
{
    use HasFilters;
    use HasSorting;
    use HasUuids;

    /**
     * Indicates if the model's ID is auto-incrementing.
     * Overridden to false to support UUIDv7 primary keys.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     * Overridden to string to support UUIDv7 primary keys.
     *
     * @var string
     */
    protected $keyType = 'string';
}
