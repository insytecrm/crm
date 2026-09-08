<?php

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\Property;

test('dashboard shows kpi cards with live counts from the database', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $activeLead = Lead::factory()->create(['status' => LeadStatus::Qualified]);
    $bookingLead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);
    Lead::factory()->create(['status' => LeadStatus::Converted]);
    Lead::factory()->create(['status' => LeadStatus::Lost]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $activeLead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);

    $property = Property::factory()->create();

    Booking::factory()->create([
        'property_id' => $property->id,
        'lead_id' => $activeLead->id,
        'agreement_date' => now()->toDateString(),
        'agreement_value' => 5000000,
        'payout_amount' => 100000,
    ]);

    Booking::factory()->create([
        'property_id' => $property->id,
        'lead_id' => $bookingLead->id,
        'agreement_date' => null,
        'agreement_value' => 2000000,
        'payout_amount' => null,
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Welcome, '.$user->name)
        ->assertSee('Total Leads')
        ->assertSee('Active Leads')
        ->assertSee('Site Visits')
        ->assertSee('Bookings')
        ->assertSee('Sales')
        ->assertViewHas('kpis', fn (array $kpis): bool => $kpis === [
            'total_leads' => 4,
            'active_leads' => 2,
            'site_visits' => 1,
            'bookings' => 2,
            'revenue' => 5000000,
        ])
        ->assertSee('5,000,000');
});

test('dashboard site visits kpi ignores closed leads and completed events', function () {
    createTestTenant();
    actingAsTenantUser();

    $activeLead = Lead::factory()->create(['status' => LeadStatus::SiteVisit]);
    $convertedLead = Lead::factory()->create(['status' => LeadStatus::Converted]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $activeLead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $activeLead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 2,
        'scheduled_at' => now()->subDay(),
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $convertedLead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertViewHas('kpis', fn (array $kpis): bool => $kpis['site_visits'] === 1
            && $kpis['active_leads'] === 1
            && $kpis['total_leads'] === 2);
});
