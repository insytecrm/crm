<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadStatus;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Property;

test('bookings index opens create booking modal with form fields', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Booking Prospect']);
    Property::factory()->create([
        'project_name' => 'Skyline Towers',
        'configurations' => [
            ['name' => '2 BHK', 'carpet_area_sqft' => 850, 'price' => 9500000, 'unit_count' => 100],
        ],
    ]);

    $this->get('/acme/bookings')
        ->assertOk()
        ->assertSee('Create Booking')
        ->assertSee('Search by lead, property, or unit...')
        ->assertSee('Related Lead')
        ->assertSee('Booking Prospect')
        ->assertSee('Select a lead')
        ->assertSee('Select a property')
        ->assertSee('Unit Number')
        ->assertSee('Agreement Value')
        ->assertSee('Booking Date')
        ->assertSee('Skyline Towers');
});

test('bookings index search filters by lead name', function () {
    createTestTenant();
    actingAsTenantUser();

    $matchingLead = Lead::factory()->create(['name' => 'Alpha Buyer']);
    $otherLead = Lead::factory()->create(['name' => 'Beta Buyer']);
    $property = Property::factory()->create(['project_name' => 'Search Towers']);

    Booking::factory()->create([
        'lead_id' => $matchingLead->id,
        'property_id' => $property->id,
        'unit_number' => '101',
    ]);
    Booking::factory()->create([
        'lead_id' => $otherLead->id,
        'property_id' => $property->id,
        'unit_number' => '202',
    ]);

    $this->get('/acme/bookings?search=Alpha')
        ->assertOk()
        ->assertSee('Alpha Buyer')
        ->assertSee('101')
        ->assertDontSee('Beta Buyer');
});

test('bookings create route redirects to index and opens modal', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();

    $this->get('/acme/bookings/create?lead='.$lead->id)
        ->assertRedirect(route('tenant.bookings.index', ['tenant' => 'acme', 'create' => 1, 'lead' => $lead->id], false));
});

test('tenant users can create a booking from the modal form', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $property = Property::factory()->create([
        'project_name' => 'Green Valley',
        'configurations' => [
            ['name' => '3 BHK', 'carpet_area_sqft' => 1200, 'price' => 15000000, 'unit_count' => 80],
        ],
    ]);

    $this->post('/acme/bookings', [
        'lead_id' => $lead->id,
        'property_id' => $property->id,
        'configuration_index' => 0,
        'unit_number' => '1502',
        'agreement_value' => 14500000,
        'booking_date' => '2026-09-01',
    ])
        ->assertRedirect(route('tenant.bookings.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    $booking = Booking::query()->first();

    expect($booking)->not->toBeNull()
        ->and($booking->lead_id)->toBe($lead->id)
        ->and($booking->property_id)->toBe($property->id)
        ->and($booking->configuration_name)->toBe('3 BHK')
        ->and($booking->unit_number)->toBe('1502')
        ->and($booking->agreement_value)->toBe(14500000)
        ->and($lead->fresh()->status)->toBe(LeadStatus::Converted);

    $this->get('/acme/bookings')
        ->assertOk()
        ->assertSee('Green Valley')
        ->assertSee('3 BHK')
        ->assertSee('1502')
        ->assertSee('14,500,000');
});

test('creating a booking for a lead logs booking activity', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Booking Lead']);
    $property = Property::factory()->create([
        'project_name' => 'Lake View',
        'configurations' => [
            ['name' => '2 BHK', 'carpet_area_sqft' => 900, 'price' => 8000000, 'unit_count' => 60],
        ],
    ]);

    $this->post('/acme/bookings', [
        'property_id' => $property->id,
        'configuration_index' => 0,
        'unit_number' => '804',
        'agreement_value' => 7800000,
        'booking_date' => '2026-09-01',
        'lead_id' => $lead->id,
    ])->assertRedirect();

    expect(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::BookingCreated)->exists())->toBeTrue();
});

test('booking creation allows only one booking per lead', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $firstProperty = Property::factory()->create([
        'configurations' => [
            ['name' => '2 BHK', 'carpet_area_sqft' => 850, 'price' => 9500000, 'unit_count' => 100],
        ],
    ]);
    $secondProperty = Property::factory()->create([
        'configurations' => [
            ['name' => '3 BHK', 'carpet_area_sqft' => 1100, 'price' => 12000000, 'unit_count' => 80],
        ],
    ]);

    Booking::factory()->create([
        'lead_id' => $lead->id,
        'property_id' => $firstProperty->id,
    ]);

    $this->from('/acme/bookings')
        ->post('/acme/bookings', [
            'lead_id' => $lead->id,
            'property_id' => $secondProperty->id,
            'configuration_index' => 0,
            'unit_number' => '202',
            'agreement_value' => 11000000,
            'booking_date' => '2026-09-02',
        ])
        ->assertRedirect('/acme/bookings')
        ->assertSessionHasErrors('lead_id');

    expect(Booking::query()->where('lead_id', $lead->id)->count())->toBe(1);
});

test('bookings list shows action icon buttons for view details and mark agreement', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create(['project_name' => 'Action Towers']);
    $booking = Booking::factory()->create([
        'property_id' => $property->id,
        'unit_number' => '901',
    ]);

    $this->get('/acme/bookings')
        ->assertOk()
        ->assertSee('Actions')
        ->assertSee('View Details')
        ->assertSee('Mark Agreement')
        ->assertSee('booking-'.$booking->id, false)
        ->assertSee('mark-agreement-'.$booking->id, false)
        ->assertDontSee('Create Invoice');
});

test('tenant users can mark agreement on a booking with payout details', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create(['payout_percent' => 2.5]);
    $booking = Booking::factory()->create([
        'property_id' => $property->id,
        'agreement_date' => null,
        'agreement_value' => 10000000,
    ]);

    $this->post('/acme/bookings/'.$booking->id.'/agreement', [
        'agreement_date' => '2026-09-15',
        'agreement_value' => 9500000,
        'payout_percent' => 2.5,
        'payout_amount' => 237500,
    ])
        ->assertRedirect(route('tenant.bookings.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    $booking->refresh();

    expect($booking->agreement_date?->toDateString())->toBe('2026-09-15')
        ->and($booking->agreement_value)->toBe(9500000)
        ->and((float) $booking->payout_percent)->toBe(2.5)
        ->and($booking->payout_amount)->toBe(237500)
        ->and(LeadActivity::query()->where('lead_id', $booking->lead_id)->where('type', LeadActivityType::AgreementMarked)->exists())->toBeTrue();
});

test('mark agreement modal shows prefilled agreement value and payout fields', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Agreement Popup Towers',
        'payout_percent' => 3,
    ]);

    Booking::factory()->create([
        'property_id' => $property->id,
        'agreement_date' => null,
        'agreement_value' => 8000000,
    ]);

    $this->get('/acme/bookings')
        ->assertOk()
        ->assertSee('Payout Amount')
        ->assertSee('Agreement Popup Towers')
        ->assertSee('name="agreement_value"', false)
        ->assertSee('name="payout_percent"', false)
        ->assertSee('name="payout_amount"', false);
});

test('create invoice option appears after agreement is marked', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'agreement_value' => 9000000,
        'payout_percent' => 2.5,
        'payout_amount' => 225000,
        'invoiced_at' => null,
    ]);

    $this->get('/acme/bookings')
        ->assertOk()
        ->assertSee('Create Invoice')
        ->assertSee('create-invoice-'.$booking->id, false)
        ->assertDontSee('Mark Agreement');
});

test('tenant users can create invoice after agreement is marked', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'agreement_value' => 9000000,
        'payout_percent' => 2.5,
        'payout_amount' => 225000,
        'invoiced_at' => null,
    ]);

    $this->post('/acme/bookings/'.$booking->id.'/invoice', [
        'invoice_date' => '2026-09-20',
    ])
        ->assertRedirect(route('tenant.bookings.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    $booking->refresh();

    expect($booking->invoice_date?->toDateString())->toBe('2026-09-20')
        ->and($booking->invoiced_at)->not->toBeNull()
        ->and(LeadActivity::query()->where('lead_id', $booking->lead_id)->where('type', LeadActivityType::InvoiceCreated)->exists())->toBeTrue();
});

test('invoice cannot be created before agreement is marked', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create(['agreement_date' => null]);

    $this->post('/acme/bookings/'.$booking->id.'/invoice', [
        'invoice_date' => '2026-09-20',
    ])->assertNotFound();
});

test('agreement cannot be marked twice', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create(['agreement_date' => '2026-09-01']);

    $this->post('/acme/bookings/'.$booking->id.'/agreement', [
        'agreement_date' => '2026-09-15',
        'agreement_value' => 9000000,
        'payout_percent' => 2.5,
        'payout_amount' => 225000,
    ])->assertNotFound();
});

test('invoices page lists invoiced bookings', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'invoice_date' => '2026-09-20',
        'invoiced_at' => now(),
        'unit_number' => '1201',
    ]);

    $booking->property->update(['project_name' => 'Invoice Towers']);

    $this->get('/acme/invoices')
        ->assertOk()
        ->assertSee('Invoice Number')
        ->assertSee('Lead Name')
        ->assertSee('Property')
        ->assertSee('Unit Number')
        ->assertSee('Agreement Value')
        ->assertSee('Invoice Amount')
        ->assertSee('Agreement Date')
        ->assertSee('Invoice Date')
        ->assertSee($booking->lead->name)
        ->assertSee('Invoice Towers')
        ->assertSee('1201')
        ->assertSee('Sep 10, 2026')
        ->assertSee('Sep 20, 2026');
});

test('booking creation requires a lead', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'configurations' => [
            ['name' => '2 BHK', 'carpet_area_sqft' => 850, 'price' => 9500000, 'unit_count' => 100],
        ],
    ]);

    $this->from('/acme/bookings')
        ->post('/acme/bookings', [
            'property_id' => $property->id,
            'configuration_index' => 0,
            'unit_number' => '101',
            'agreement_value' => 9000000,
            'booking_date' => '2026-09-01',
        ])
        ->assertRedirect('/acme/bookings')
        ->assertSessionHasErrors('lead_id');

    expect(Booking::query()->count())->toBe(0);
});

test('booking creation rejects invalid configuration for property', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $property = Property::factory()->create([
        'configurations' => [
            ['name' => '2 BHK', 'carpet_area_sqft' => 850, 'price' => 9500000, 'unit_count' => 100],
        ],
    ]);

    $this->from('/acme/bookings')
        ->post('/acme/bookings', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'configuration_index' => 5,
            'unit_number' => '101',
            'agreement_value' => 9000000,
            'booking_date' => '2026-09-01',
        ])
        ->assertRedirect('/acme/bookings')
        ->assertSessionHasErrors('configuration_index');

    expect(Booking::query()->count())->toBe(0);
});
