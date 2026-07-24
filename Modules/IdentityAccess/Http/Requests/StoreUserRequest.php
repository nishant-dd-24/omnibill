<?php

declare(strict_types=1);

namespace Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\IdentityAccess\Domain\Enums\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', new Enum(Role::class)],
        ];
    }

    public function validatedRole(): Role
    {
        /** @var string $role */
        $role = $this->validated('role');

        return Role::from($role);
    }
}
