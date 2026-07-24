<?php

declare(strict_types=1);

namespace Modules\Webhook\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookConfigRequest extends FormRequest
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
            'webhook_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
