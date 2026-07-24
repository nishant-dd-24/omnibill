<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invoice\Application\Services\IssueCreditNoteService;
use Modules\Invoice\Domain\Models\Invoice;

uses(RefreshDatabase::class);

it('issues a credit note', function () {
    $invoice = Invoice::factory()->create(['status' => 'open', 'total' => 1000, 'amount_due' => 1000]);
    $service = new IssueCreditNoteService;

    $creditNote = $service->execute($invoice, 500, 'Test reason');

    expect($creditNote->invoice_id)->toBe($invoice->id)
        ->and($creditNote->amount)->toBe(500)
        ->and($creditNote->reason)->toBe('Test reason');
});

it('throws exception if credit note amount exceeds amount due', function () {
    $invoice = Invoice::factory()->create(['status' => 'open', 'total' => 1000, 'amount_due' => 400]);
    $service = new IssueCreditNoteService;

    expect(fn () => $service->execute($invoice, 500, 'Test reason'))
        ->toThrow(DomainException::class, 'Credit note amount cannot exceed invoice amount due.');
});
