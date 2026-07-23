<?php

namespace Modules\Shared\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Shared\Domain\Context\CorrelationId;

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
                'correlation_id' => app()->bound(CorrelationId::class) ? app(CorrelationId::class)->id() : null,
            ],
        ];
    }
}
