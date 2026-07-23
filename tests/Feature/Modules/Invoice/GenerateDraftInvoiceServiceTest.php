<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Invoice\Application\Services\GenerateDraftInvoiceService;

uses(RefreshDatabase::class);

it('generates a draft invoice with line items', function () {
    $service = new GenerateDraftInvoiceService;

    $tenantId = Str::uuid()->toString();
    $customerId = Str::uuid()->toString();
    $subscriptionId = Str::uuid()->toString();

    $lineItemsData = [
        [
            'description' => 'Test Item 1',
            'quantity' => 2,
            'unit_amount' => 1000,
            'tax_amount' => 200,
        ],
        [
            'description' => 'Test Item 2',
            'quantity' => 1,
            'unit_amount' => 500,
            'tax_amount' => 50,
        ],
    ];

    $invoice = $service->execute($tenantId, $customerId, $subscriptionId, $lineItemsData, 'USD');

    expect($invoice->status)->toBe('draft')
        ->and($invoice->currency)->toBe('USD')
        ->and($invoice->subtotal)->toBe(2500)
        ->and($invoice->tax_total)->toBe(250)
        ->and($invoice->total)->toBe(2750)
        ->and($invoice->lineItems)->toHaveCount(2);
});
