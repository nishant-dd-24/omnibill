<?php

declare(strict_types=1);

namespace Modules\Subscription\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Subscription\Domain\Models\Price;

/**
 * @mixin Price
 */
class PriceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'interval' => $this->interval,
            'interval_count' => $this->interval_count,
            'stripe_price_id' => $this->stripe_price_id,
            'is_active' => $this->is_active,
        ];
    }
}
