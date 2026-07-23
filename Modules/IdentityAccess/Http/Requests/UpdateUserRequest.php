<?php

namespace Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\IdentityAccess\Domain\Models\User;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.($user ? $user->id : '')],
        ];
    }
}
