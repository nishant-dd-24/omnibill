<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payment\Domain\Models\Payment;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'customer_id' => $this->customer_id,
            'payment_method_id' => $this->payment_method_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
        ];
    }
}
