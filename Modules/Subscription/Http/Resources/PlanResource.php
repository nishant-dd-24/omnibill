<?php

declare(strict_types=1);

namespace Modules\Subscription\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Subscription\Domain\Models\Plan;

/**
 * @mixin Plan
 */
class PlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'features' => $this->features,
            'prices' => PriceResource::collection($this->whenLoaded('prices')),
            'stripe_product_id' => $this->stripe_product_id,
            'is_active' => $this->is_active,
        ];
    }
}
