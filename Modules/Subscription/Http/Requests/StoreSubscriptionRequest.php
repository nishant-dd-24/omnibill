<?php

declare(strict_types=1);

namespace Modules\Subscription\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'string', 'uuid'],
            'plan_id' => ['required', 'string', 'uuid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.price_id' => ['required', 'string', 'uuid'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
