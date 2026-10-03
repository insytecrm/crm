<?php

use App\Enums\LeadActivityType;
use App\Models\Booking;
use App\Models\LeadActivity;
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
        ->assertSee('Invoice Number')
        ->assertSee('Lead Name')
        ->assertSee($invoice->lead->name)
        ->assertSee('Actions')
        ->assertSee('View Invoice')
        ->assertSee('Download PDF')
        ->assertSee('Mark Paid')
        ->assertSee('Create Invoice')
        ->assertSee('Search by invoice, lead, property, or unit...')
        ->assertSee('Filters')
        ->assertSee('Payment Status')
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

test('invoices page can create an invoice from an agreed booking', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create(['project_name' => 'Ready To Invoice Heights']);
    $booking = Booking::factory()->create([
        'property_id' => $property->id,
        'agreement_date' => '2026-09-10',
        'agreement_value' => 8000000,
        'payout_amount' => 200000,
        'invoiced_at' => null,
        'unit_number' => '1102',
    ]);

    $this->get('/acme/invoices')
        ->assertOk()
        ->assertSee('Ready To Invoice Heights')
        ->assertSee('1102')
        ->assertDontSee(Booking::invoiceNumberFor($booking->id));

    $this->from('/acme/invoices')
        ->post('/acme/invoices', [
            'booking_id' => $booking->id,
            'invoice_date' => '2026-09-22',
        ])
        ->assertRedirect(route('tenant.invoices.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    $booking->refresh();

    expect($booking->hasInvoice())->toBeTrue()
        ->and($booking->invoice_date?->toDateString())->toBe('2026-09-22')
        ->and($booking->invoice_number)->toBe(Booking::invoiceNumberFor($booking->id))
        ->and(LeadActivity::query()->where('lead_id', $booking->lead_id)->where('type', LeadActivityType::InvoiceCreated)->exists())->toBeTrue();
});

test('creating an invoice from invoices rejects bookings without an agreement', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create([
        'agreement_date' => null,
        'invoiced_at' => null,
    ]);

    $this->from('/acme/invoices')
        ->post('/acme/invoices', [
            'booking_id' => $booking->id,
            'invoice_date' => '2026-09-22',
        ])
        ->assertRedirect('/acme/invoices')
        ->assertSessionHasErrors('booking_id');

    expect($booking->fresh()->hasInvoice())->toBeFalse();
});

test('tenant users can mark an invoice as paid', function () {
    createTestTenant();
    actingAsTenantUser();

    $invoice = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'payout_amount' => 150000,
        'payout_paid_at' => null,
        'invoice_date' => '2026-09-20',
        'invoiced_at' => now(),
    ]);

    $this->post('/acme/invoices/'.$invoice->id.'/mark-paid')
        ->assertRedirect(route('tenant.invoices.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    expect($invoice->fresh()->payout_paid_at)->not->toBeNull()
        ->and(LeadActivity::query()->where('lead_id', $invoice->lead_id)->where('type', LeadActivityType::PayoutReceived)->exists())->toBeTrue();
});

test('mark paid is unavailable before invoice is created', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'payout_amount' => 150000,
        'payout_paid_at' => null,
        'invoiced_at' => null,
    ]);

    $this->post('/acme/invoices/'.$booking->id.'/mark-paid')->assertNotFound();
});

test('invoice payment filter shows only pending invoices', function () {
    createTestTenant();
    actingAsTenantUser();

    $pending = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'invoice_date' => '2026-09-20',
        'invoice_number' => 'INV-PENDING',
        'invoiced_at' => now(),
        'payout_paid_at' => null,
    ]);

    Booking::factory()->create([
        'agreement_date' => '2026-09-11',
        'invoice_date' => '2026-09-21',
        'invoice_number' => 'INV-PAID',
        'invoiced_at' => now(),
        'payout_paid_at' => now(),
    ]);

    $this->get('/acme/invoices?payment=pending')
        ->assertOk()
        ->assertSee('INV-PENDING')
        ->assertSee($pending->lead->name)
        ->assertDontSee('INV-PAID');
});
