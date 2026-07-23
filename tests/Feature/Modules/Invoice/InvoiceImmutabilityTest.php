<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Invoice\Domain\Models\InvoiceLineItem;

uses(RefreshDatabase::class);

it('allows modifying line items for draft invoices', function () {
    $invoice = Invoice::factory()->create(['status' => 'draft']);
    $lineItem = InvoiceLineItem::factory()->create(['invoice_id' => $invoice->id]);

    $lineItem->update(['quantity' => 5]);

    $lineItem->refresh();
    expect($lineItem->quantity)->toBe(5);
});

it('prevents modifying line items for non-draft invoices', function () {
    $invoice = Invoice::factory()->create(['status' => 'open']);
    $lineItem = InvoiceLineItem::factory()->create(['invoice_id' => $invoice->id]);
    actingAsTenant($invoice->tenant_id);
    
    expect(fn () => $lineItem->update(['quantity' => $lineItem->quantity + 5]))
        ->toThrow(\DomainException::class, 'Cannot modify line items of a finalized invoice.');
});

it('prevents deleting line items for non-draft invoices', function () {
    $invoice = Invoice::factory()->create(['status' => 'open']);
    $lineItem = InvoiceLineItem::factory()->create(['invoice_id' => $invoice->id]);
    actingAsTenant($invoice->tenant_id);
    
    expect(fn () => $lineItem->delete())
        ->toThrow(DomainException::class, 'Cannot delete line items of a finalized invoice.');
});
