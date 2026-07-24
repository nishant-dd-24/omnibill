<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Invoice\Application\Adapters\InvoiceRendererInterface;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Invoice\Infrastructure\Jobs\GenerateInvoicePdfJob;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

it('generates a pdf and updates the invoice', function () {
    Storage::fake('s3');

    $tenant = Tenant::create([
        'name' => 'Test Tenant',
        'status' => 'active',
    ]);
    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenant->id,
        'number' => 'INV-001',
    ]);

    $rendererMock = Mockery::mock(InvoiceRendererInterface::class);
    $rendererMock->shouldReceive('render')
        ->once()
        ->with(Mockery::on(fn ($arg) => $arg->id === $invoice->id))
        ->andReturn('fake-pdf-content');

    $this->app->instance(InvoiceRendererInterface::class, $rendererMock);

    GenerateInvoicePdfJob::dispatchSync($tenant->id, $invoice->id);

    $invoice->refresh();

    expect($invoice->pdf_url)->not->toBeNull()
        ->and($invoice->pdf_url)->toContain('INV-001');

    Storage::disk('s3')->assertExists($invoice->pdf_url);
    expect(Storage::disk('s3')->get($invoice->pdf_url))->toBe('fake-pdf-content');
});
