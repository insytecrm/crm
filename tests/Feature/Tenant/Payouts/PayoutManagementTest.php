<?php

use App\Models\Booking;
use App\Models\Property;

test('payouts list shows action buttons and payout details', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Payout Towers',
        'payout_percent' => 2.5,
    ]);

    $payout = Booking::factory()->create([
        'property_id' => $property->id,
        'agreement_date' => '2026-09-10',
        'agreement_value' => 10000000,
        'payout_percent' => 2.5,
        'payout_amount' => 250000,
        'unit_number' => '901',
        'invoice_date' => '2026-09-15',
        'invoiced_at' => now(),
    ]);

    $this->get('/acme/payouts')
        ->assertOk()
        ->assertSee('Payout Towers')
        ->assertSee('901')
        ->assertSee('2.50%')
        ->assertSee('250,000')
        ->assertSee('Actions')
        ->assertSee('View Details')
        ->assertSee('Mark Paid')
        ->assertSee('payout-'.$payout->id, false);
});

test('payouts list does not show mark paid before invoice is created', function () {
    createTestTenant();
    actingAsTenantUser();

    Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'agreement_value' => 8000000,
        'payout_percent' => 3,
        'payout_amount' => 240000,
        'invoiced_at' => null,
    ]);

    $this->get('/acme/payouts')
        ->assertOk()
        ->assertSee('Payout Details')
        ->assertSee('Pending Payment')
        ->assertDontSee('Mark Paid');
});

test('payout details modal shows payout information', function () {
    createTestTenant();
    actingAsTenantUser();

    Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'agreement_value' => 8000000,
        'payout_percent' => 3,
        'payout_amount' => 240000,
        'invoice_date' => '2026-09-20',
        'invoiced_at' => now(),
    ]);

    $this->get('/acme/payouts')
        ->assertOk()
        ->assertSee('Payout Details')
        ->assertSee('Pending Payment')
        ->assertSee('Payment Status');
});

test('tenant users can mark a payout as paid after invoice is created', function () {
    createTestTenant();
    actingAsTenantUser();

    $payout = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'payout_amount' => 150000,
        'payout_paid_at' => null,
        'invoice_date' => '2026-09-20',
        'invoiced_at' => now(),
    ]);

    $this->post('/acme/payouts/'.$payout->id.'/mark-paid')
        ->assertRedirect(route('tenant.payouts.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    expect($payout->fresh()->payout_paid_at)->not->toBeNull();
});

test('mark paid is unavailable before invoice is created', function () {
    createTestTenant();
    actingAsTenantUser();

    $payout = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'payout_amount' => 150000,
        'payout_paid_at' => null,
        'invoiced_at' => null,
    ]);

    $this->post('/acme/payouts/'.$payout->id.'/mark-paid')->assertNotFound();
});

test('mark paid is unavailable after payout is already paid', function () {
    createTestTenant();
    actingAsTenantUser();

    $payout = Booking::factory()->create([
        'agreement_date' => '2026-09-10',
        'payout_paid_at' => now(),
        'invoice_date' => '2026-09-20',
        'invoiced_at' => now(),
    ]);

    $this->post('/acme/payouts/'.$payout->id.'/mark-paid')->assertNotFound();
});

test('paid payouts are excluded from pending payout revenue total', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create(['payout_percent' => 2.0]);

    Booking::factory()->create([
        'property_id' => $property->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 5000000,
        'payout_percent' => 2.0,
        'payout_amount' => 100000,
        'payout_paid_at' => now(),
    ]);

    Booking::factory()->create([
        'property_id' => $property->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 3000000,
        'payout_percent' => 2.0,
        'payout_amount' => 60000,
        'payout_paid_at' => null,
    ]);

    $this->get('/acme/revenue')
        ->assertOk()
        ->assertSee('₹8,000,000')
        ->assertSee('₹60,000')
        ->assertSee('Pending Commission');
});

test('bookings without agreement do not appear on payouts list', function () {
    createTestTenant();
    actingAsTenantUser();

    Booking::factory()->create(['agreement_date' => null]);

    $this->get('/acme/payouts')
        ->assertOk()
        ->assertSee('No payouts yet.');
});
