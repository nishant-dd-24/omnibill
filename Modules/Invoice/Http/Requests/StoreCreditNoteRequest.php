<?php

declare(strict_types=1);

namespace Modules\Invoice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Invoice\Domain\Models\Invoice;

class StoreCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        /** @var Invoice|null $invoice */
        $invoice = $this->route('invoice');

        $maxAmount = $invoice ? $invoice->amount_due : 0;

        return [
            'amount' => ['required', 'integer', 'min:1', "max:$maxAmount"],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
