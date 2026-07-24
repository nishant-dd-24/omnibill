<?php

declare(strict_types=1);

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Invoice\Application\Services\IssueCreditNoteService;
use Modules\Invoice\Domain\Models\CreditNote;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Invoice\Http\Requests\StoreCreditNoteRequest;
use Modules\Invoice\Http\Resources\CreditNoteResource;

class CreditNoteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $creditNotes = CreditNote::cursorPaginate($request->query('per_page', 15));

        return CreditNoteResource::collection($creditNotes);
    }

    public function show(CreditNote $creditNote): CreditNoteResource
    {
        return new CreditNoteResource($creditNote);
    }

    public function store(StoreCreditNoteRequest $request, Invoice $invoice, IssueCreditNoteService $issueCreditNoteService): CreditNoteResource
    {
        /** @var string|null $reason */
        $reason = $request->input('reason');

        $creditNote = $issueCreditNoteService->execute(
            $invoice,
            $request->integer('amount'),
            $reason
        );

        return new CreditNoteResource($creditNote);
    }
}
