<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Customer\Domain\Models\Customer;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Invoice\Domain\Models\CreditNote;
use Modules\Invoice\Domain\Models\Invoice;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Domain\Models\Tenant;

uses(RefreshDatabase::class);

function setupCreditNoteTest(): array
{
    $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

    $currentTenant = new CurrentTenant($tenant->id);
    app()->instance(CurrentTenant::class, $currentTenant);
    test()->withHeaders(['X-Tenant-ID' => $tenant->id]);

    return [$tenant->id, $user, $customer];
}

it('can list credit notes', function () {
    [$tenantId, $user, $customer] = setupCreditNoteTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'status' => 'open',
    ]);

    $creditNote = CreditNote::factory()->create([
        'tenant_id' => $tenantId,
        'invoice_id' => $invoice->id,
    ]);

    $response = test()->actingAs($user)->getJson('/api/v1/credit-notes');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $creditNote->id);
});

it('can show a credit note', function () {
    [$tenantId, $user, $customer] = setupCreditNoteTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'status' => 'open',
    ]);

    $creditNote = CreditNote::factory()->create([
        'tenant_id' => $tenantId,
        'invoice_id' => $invoice->id,
    ]);

    $response = test()->actingAs($user)->getJson("/api/v1/credit-notes/{$creditNote->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $creditNote->id);
});

it('prevents accessing cross-tenant credit note (IDOR)', function () {
    [$tenantId, $user, $customer] = setupCreditNoteTest();

    $otherTenant = Tenant::create(['name' => 'Other Tenant', 'status' => 'active']);
    $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherInvoice = Invoice::factory()->create([
        'tenant_id' => $otherTenant->id,
        'customer_id' => $otherCustomer->id,
    ]);

    $otherCreditNote = CreditNote::factory()->create([
        'tenant_id' => $otherTenant->id,
        'invoice_id' => $otherInvoice->id,
    ]);

    $response = test()->actingAs($user)->getJson("/api/v1/credit-notes/{$otherCreditNote->id}");

    $response->assertStatus(404);
});

it('can store a credit note', function () {
    [$tenantId, $user, $customer] = setupCreditNoteTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'status' => 'open',
        'amount_due' => 1000,
    ]);

    $response = test()->actingAs($user)
        ->withHeaders(['Idempotency-Key' => Str::uuid()->toString()])
        ->postJson("/api/v1/invoices/{$invoice->id}/credit-notes", [
            'amount' => 500,
            'reason' => 'Customer dissatisfaction',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.amount', 500)
        ->assertJsonPath('data.invoice_id', $invoice->id);
});

it('uses idempotency for storing credit note', function () {
    [$tenantId, $user, $customer] = setupCreditNoteTest();

    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenantId,
        'customer_id' => $customer->id,
        'status' => 'open',
        'amount_due' => 1000,
    ]);

    $idempotencyKey = Str::uuid()->toString();

    // First request
    $response1 = test()->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $idempotencyKey])
        ->postJson("/api/v1/invoices/{$invoice->id}/credit-notes", [
            'amount' => 500,
            'reason' => 'Duplicate charge',
        ]);

    $response1->assertStatus(201);

    // Second request with same key
    $response2 = test()->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $idempotencyKey])
        ->postJson("/api/v1/invoices/{$invoice->id}/credit-notes", [
            'amount' => 500,
            'reason' => 'Duplicate charge',
        ]);

    $response2->assertStatus(201);

    // Validate we got the cached response
    $this->assertEquals($response1->json(), $response2->json());

    // Validate it didn't create two credit notes
    $this->assertDatabaseCount('credit_notes', 1);
});
