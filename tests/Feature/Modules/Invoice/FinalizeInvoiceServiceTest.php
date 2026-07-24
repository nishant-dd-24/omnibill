<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Invoice\Application\Services\FinalizeInvoiceService;
use Modules\Invoice\Domain\Events\InvoiceFinalized;
use Modules\Invoice\Domain\Models\Invoice;

uses(RefreshDatabase::class);

it('finalizes a draft invoice', function () {
    Event::fake();

    $invoice = Invoice::factory()->create(['status' => 'draft']);
    $service = new FinalizeInvoiceService;

    $service->execute($invoice);

    expect($invoice->status)->toBe('open')
        ->and($invoice->finalized_at)->not->toBeNull();

    Event::assertDispatched(InvoiceFinalized::class, function ($event) use ($invoice) {
        return $event->invoice->id === $invoice->id;
    });
});

it('throws exception if invoice is already finalized', function () {
    $invoice = Invoice::factory()->create(['status' => 'open']);
    $service = new FinalizeInvoiceService;

    expect(fn () => $service->execute($invoice))
        ->toThrow(DomainException::class, 'Only draft invoices can be finalized.');
});
