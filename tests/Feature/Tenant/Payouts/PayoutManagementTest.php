<?php

use App\Enums\LeadActivityType;
use App\Models\Booking;
use App\Models\LeadActivity;
use App\Models\Property;

test('payouts page redirects to invoices', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/payouts')
        ->assertRedirect(route('tenant.invoices.index', ['tenant' => 'acme'], false));
});

test('legacy mark paid route still records payment and redirects to invoices', function () {
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
        ->assertRedirect(route('tenant.invoices.index', ['tenant' => 'acme'], false))
        ->assertSessionHas('status');

    expect($payout->fresh()->payout_paid_at)->not->toBeNull()
        ->and(LeadActivity::query()->where('lead_id', $payout->lead_id)->where('type', LeadActivityType::PayoutReceived)->exists())->toBeTrue();
});

test('legacy mark paid is unavailable before invoice is created', function () {
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

test('legacy mark paid is unavailable after payout is already paid', function () {
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
