<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Invoice\Domain\Events\InvoiceFinalized;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

function setupInvoiceTest(): array
{
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

    $currentTenant = new CurrentTenant($tenant->id);
    app()->instance(CurrentTenant::class, $currentTenant);
    test()->withHeaders(['X-Tenant-ID' => $tenant->id]);

    return [$tenant->id, $user, $customer];
}

it('can list invoices', function () {
    [$tenantId, $user, $customer] = setupInvoiceTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'status' => 'draft',
    ]);

    $response = test()->actingAs($user)->getJson('/api/v1/invoices');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $invoice->id);
});

it('can show an invoice', function () {
    [$tenantId, $user, $customer] = setupInvoiceTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
    ]);

    $response = test()->actingAs($user)->getJson("/api/v1/invoices/{$invoice->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $invoice->id);
});

it('prevents accessing cross-tenant invoice (IDOR)', function () {
    [$tenantId, $user, $customer] = setupInvoiceTest();

    $otherTenant = Tenant::create(['name' => 'Other Tenant', 'status' => 'active']);
    $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherInvoice = Invoice::factory()->create([
        'tenant_id' => $otherTenant->id,
        'customer_id' => $otherCustomer->id,
    ]);

    $response = test()->actingAs($user)->getJson("/api/v1/invoices/{$otherInvoice->id}");

    $response->assertStatus(404);
});

it('can finalize an invoice', function () {
    Event::fake([InvoiceFinalized::class]);

    [$tenantId, $user, $customer] = setupInvoiceTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'status' => 'draft',
    ]);

    $response = test()->actingAs($user)
        ->withHeaders(['Idempotency-Key' => Str::uuid()->toString()])
        ->postJson("/api/v1/invoices/{$invoice->id}/finalize");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'open');

    Event::assertDispatched(InvoiceFinalized::class, function ($event) use ($invoice) {
        return $event->invoice->id === $invoice->id;
    });
});

it('uses idempotency for finalizing invoice', function () {
    [$tenantId, $user, $customer] = setupInvoiceTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'status' => 'draft',
    ]);

    $idempotencyKey = Str::uuid()->toString();

    // First request
    $response1 = test()->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $idempotencyKey])
        ->postJson("/api/v1/invoices/{$invoice->id}/finalize");

    $response1->assertStatus(200)
        ->assertJsonPath('data.status', 'open');

    // Second request with same key
    $response2 = test()->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $idempotencyKey])
        ->postJson("/api/v1/invoices/{$invoice->id}/finalize");

    $response2->assertStatus(200)
        ->assertJsonPath('data.status', 'open');

    // Validate we got the cached response (or at least it succeeded without throwing exception)
    $this->assertEquals($response1->json(), $response2->json());
});

it('can download invoice pdf', function () {
    [$tenantId, $user, $customer] = setupInvoiceTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
    ]);

    $response = test()->actingAs($user)->get("/api/v1/invoices/{$invoice->id}/pdf");

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'attachment; filename="invoice-'.$invoice->number.'.pdf"');
});
