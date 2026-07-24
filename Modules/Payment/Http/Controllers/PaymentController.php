<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Http\Resources\PaymentResource;

class PaymentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $payments = Payment::latest()->cursorPaginate();

        return PaymentResource::collection($payments);
    }

    public function show(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment);
    }
}
