<?php

use App\Models\Booking;
use App\Models\Property;

test('revenue page shows summary kpis trend chart project table and salespeople chart', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $property = Property::factory()->create([
        'developer_name' => 'Skyline Developers',
        'project_name' => 'Skyline Heights',
        'payout_percent' => 2.5,
    ]);

    Booking::factory()->create([
        'property_id' => $property->id,
        'created_by_id' => $admin->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 10000000,
        'payout_percent' => 2.5,
        'payout_amount' => 250000,
        'payout_paid_at' => null,
    ]);

    Booking::factory()->create([
        'property_id' => $property->id,
        'created_by_id' => $admin->id,
        'agreement_date' => now()->subMonth()->toDateString(),
        'agreement_value' => 5000000,
        'payout_percent' => 2.5,
        'payout_amount' => 125000,
        'payout_paid_at' => now(),
    ]);

    $this->get('/acme/revenue')
        ->assertOk()
        ->assertSee('Filters')
        ->assertSee('Revenue Summary')
        ->assertSee('Total Revenue')
        ->assertSee('Revenue This Month')
        ->assertSee('Revenue This Quarter')
        ->assertSee('Total Commission')
        ->assertSee('Pending Commission')
        ->assertSee('Received Commission')
        ->assertSee('Revenue Trend')
        ->assertSee('Revenue by Project')
        ->assertSee('Top Performing Salespeople')
        ->assertSee('Skyline Heights')
        ->assertSee('Skyline Developers')
        ->assertSee($admin->name)
        ->assertSee('15,000,000')
        ->assertSee('375,000')
        ->assertSee('250,000')
        ->assertSee('125,000');
});

test('revenue filters scope all dashboard sections', function () {
    createTestTenant();
    actingAsTenantUser();

    $includedProperty = Property::factory()->create([
        'developer_name' => 'Included Developer',
        'project_name' => 'Included Project',
    ]);

    $excludedProperty = Property::factory()->create([
        'developer_name' => 'Excluded Developer',
        'project_name' => 'Excluded Project',
    ]);

    Booking::factory()->create([
        'property_id' => $includedProperty->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 7000000,
        'payout_amount' => 140000,
    ]);

    Booking::factory()->create([
        'property_id' => $excludedProperty->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 3000000,
        'payout_amount' => 60000,
    ]);

    $this->get('/acme/revenue?developer=Included+Developer')
        ->assertOk()
        ->assertSee('7,000,000')
        ->assertSee('140,000')
        ->assertSee('Included Project')
        ->assertDontSee('3,000,000');
});

test('revenue project filter limits all sections', function () {
    createTestTenant();
    actingAsTenantUser();

    $alpha = Property::factory()->create(['project_name' => 'Alpha Residency']);
    $beta = Property::factory()->create(['project_name' => 'Beta Greens']);

    Booking::factory()->create([
        'property_id' => $alpha->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 4000000,
        'payout_amount' => 80000,
    ]);

    Booking::factory()->create([
        'property_id' => $beta->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 9000000,
        'payout_amount' => 180000,
    ]);

    $this->get('/acme/revenue?property='.$alpha->id)
        ->assertOk()
        ->assertSee('Alpha Residency')
        ->assertSee('4,000,000')
        ->assertDontSee('9,000,000');
});

test('revenue page excludes bookings without agreement from analytics', function () {
    createTestTenant();
    actingAsTenantUser();

    Booking::factory()->create([
        'agreement_date' => null,
        'agreement_value' => 12000000,
        'payout_amount' => 240000,
    ]);

    $this->get('/acme/revenue')
        ->assertOk()
        ->assertSee('Total Revenue')
        ->assertSee('₹0')
        ->assertSee('No project revenue data for the selected filters.');
});
