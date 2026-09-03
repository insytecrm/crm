<?php

use App\Models\Booking;
use App\Models\Property;

test('invoices list shows view and download pdf action buttons', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create(['project_name' => 'Invoice Action Towers']);
    $invoice = Booking::factory()->create([
        'property_id' => $property->id,
        'agreement_date' => '2026-09-10',
        'invoice_date' => '2026-09-20',
        'invoice_number' => 'INV-00021',
        'invoiced_at' => now(),
    ]);

    $this->get('/acme/invoices')
        ->assertOk()
        ->assertSee('Actions')
        ->assertSee('View Invoice')
        ->assertSee('Download PDF')
        ->assertSee('invoice-'.$invoice->id, false)
        ->assertSee(route('tenant.invoices.pdf', ['tenant' => 'acme', 'booking' => $invoice->id], false));
});

test('tenant users can download an invoice pdf', function () {
    createTestTenant();
    actingAsTenantUser();

    $invoice = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'invoice_date' => '2026-09-20',
        'invoice_number' => 'INV-00099',
        'payout_amount' => 250000,
        'invoiced_at' => now(),
        'unit_number' => '808',
    ]);

    $response = $this->get('/acme/invoices/'.$invoice->id.'/pdf');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect(str_starts_with($response->getContent(), '%PDF-1.4'))->toBeTrue()
        ->and($response->headers->get('content-disposition'))->toContain('INV-00099.pdf');
});

test('invoice view modal includes edit invoice form', function () {
    createTestTenant();
    actingAsTenantUser();

    $invoice = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'invoice_date' => '2026-09-20',
        'invoice_number' => 'INV-00101',
        'invoiced_at' => now(),
    ]);

    $this->get('/acme/invoices')
        ->assertOk()
        ->assertSee('Edit Invoice')
        ->assertSee('Invoice Number')
        ->assertSee('Optional invoice notes');
});

test('tenant users can update an invoice from the view modal', function () {
    createTestTenant();
    actingAsTenantUser();

    $invoice = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'invoice_date' => '2026-09-20',
        'invoice_number' => 'INV-00200',
        'agreement_value' => 9000000,
        'payout_percent' => 2.5,
        'payout_amount' => 225000,
        'invoiced_at' => now(),
    ]);

    $this->from('/acme/invoices')
        ->patch('/acme/invoices/'.$invoice->id, [
            'invoice_number' => 'INV-00200-A',
            'invoice_date' => '2026-09-25',
            'agreement_value' => 9500000,
            'payout_percent' => 2.5,
            'payout_amount' => 237500,
            'invoice_notes' => 'Revised payout terms.',
        ])
        ->assertRedirect(route('tenant.invoices.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    $invoice->refresh();

    expect($invoice->invoice_number)->toBe('INV-00200-A')
        ->and($invoice->invoice_date?->toDateString())->toBe('2026-09-25')
        ->and($invoice->agreement_value)->toBe(9500000)
        ->and($invoice->payout_amount)->toBe(237500)
        ->and($invoice->invoice_notes)->toBe('Revised payout terms.');
});

test('invoice pdf download is unavailable before invoice is created', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'invoiced_at' => null,
    ]);

    $this->get('/acme/invoices/'.$booking->id.'/pdf')->assertNotFound();
});
