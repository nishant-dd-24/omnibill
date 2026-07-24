<?php

declare(strict_types=1);

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Modules\Invoice\Application\Adapters\InvoiceRendererInterface;
use Modules\Invoice\Application\Services\FinalizeInvoiceService;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Invoice\Http\Resources\InvoiceResource;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $invoices = Invoice::with(['lineItems'])->cursorPaginate($request->query('per_page', 15));

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $invoice->load(['lineItems', 'creditNotes']);

        return new InvoiceResource($invoice);
    }

    public function finalize(Invoice $invoice, FinalizeInvoiceService $finalizeInvoiceService): InvoiceResource
    {
        $invoice = $finalizeInvoiceService->execute($invoice);

        $invoice->load(['lineItems']);

        return new InvoiceResource($invoice);
    }

    public function downloadPdf(Invoice $invoice, InvoiceRendererInterface $renderer): Response
    {
        $pdfContent = $renderer->render($invoice);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-'.$invoice->number.'.pdf"',
        ]);
    }
}
