<?php

namespace Modules\IdentityAccess\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\IdentityAccess\Domain\Enums\Role;

class UserRole extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => Role::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
