<?php

namespace Modules\Shared\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Context;

abstract class BaseResource extends JsonResource
{
    /**
     * Add standard meta data to the resource response.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function with($request): array
    {
        return [
            'meta' => [
                'correlation_id' => Context::get('correlation_id'),
            ],
        ];
    }
}
