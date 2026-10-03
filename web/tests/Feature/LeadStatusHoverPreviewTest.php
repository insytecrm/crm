<?php

use App\Enums\LeadLostReason;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitType;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\Property;
use App\Support\LeadStatusHoverPreview;

test('site visit status hover preview includes schedule and property details', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Hover Towers',
        'developer_name' => 'Hover Dev',
    ]);
    $lead = Lead::factory()->create([
        'name' => 'Hover Site Visit Lead',
        'status' => LeadStatus::SiteVisit,
        'upcoming_site_visit_at' => now()->addDay()->startOfHour(),
    ]);
    $scheduledAt = now()->addDay()->startOfHour();

    scheduleSiteVisitForLead($lead, [
        'property_id' => $property->id,
        'visit_type' => SiteVisitType::FreshVisit,
        'scheduled_at' => $scheduledAt,
        'priority' => ScheduledActivityPriority::High,
        'notes' => 'Bring brochure',
    ]);

    $lead->load(['scheduledEvents.property', 'assignedTo', 'completedSiteVisitEvents.property', 'latestBooking.property']);

    $preview = LeadStatusHoverPreview::for($lead);

    expect($preview['status'])->toBe('Site Visit')
        ->and(collect($preview['fields'])->pluck('value', 'label')->all())->toMatchArray([
            'Activity' => '1st Site Visit · Fresh Visit',
            'State' => 'Scheduled',
            'Date & Time' => $scheduledAt->format('M j, Y g:i A'),
            'Property' => 'Hover Towers · Hover Dev',
            'Visit type' => 'Fresh Visit',
            'Priority' => 'High',
        ]);
});

test('converted status hover preview includes booking details', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Booked Heights',
        'developer_name' => 'Booked Dev',
    ]);
    $lead = Lead::factory()->create([
        'status' => LeadStatus::Converted,
        'closed_at' => now()->subDay(),
    ]);
    Booking::factory()->create([
        'lead_id' => $lead->id,
        'property_id' => $property->id,
        'unit_number' => 'A-1204',
        'configuration_name' => '3 BHK',
        'agreement_value' => 8500000,
        'booking_date' => now()->subDays(2)->toDateString(),
    ]);

    $lead->load(['latestBooking.property', 'scheduledEvents.property', 'assignedTo', 'completedSiteVisitEvents.property']);

    $preview = LeadStatusHoverPreview::for($lead);
    $fields = collect($preview['fields'])->pluck('value', 'label');

    expect($preview['status'])->toBe('Converted')
        ->and($fields->get('Property'))->toBe('Booked Heights · Booked Dev')
        ->and($fields->get('Unit'))->toBe('A-1204')
        ->and($fields->get('Configuration'))->toBe('3 BHK')
        ->and($fields->get('Agreement value'))->toBe('₹8,500,000');
});

test('lost status hover preview includes reasons and notes', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::Lost,
        'lost_reasons' => [LeadLostReason::BudgetMismatch->value, LeadLostReason::NotInterested->value],
        'closing_notes' => 'Asked to pause outreach',
        'closed_at' => now()->subHours(5),
    ]);

    $preview = LeadStatusHoverPreview::for($lead->fresh());
    $fields = collect($preview['fields'])->pluck('value', 'label');

    expect($preview['status'])->toBe('Lost')
        ->and($fields->get('Reasons'))->toContain('Budget Mismatch')
        ->and($fields->get('Reasons'))->toContain('Not Interested')
        ->and($fields->get('Notes'))->toBe('Asked to pause outreach');
});

test('leads index renders status hover details for site visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Index Hover Property',
        'developer_name' => 'Index Dev',
    ]);
    $lead = Lead::factory()->create([
        'name' => 'Index Hover Lead',
        'status' => LeadStatus::SiteVisit,
    ]);
    scheduleSiteVisitForLead($lead, [
        'property_id' => $property->id,
        'visit_type' => SiteVisitType::Revisit,
        'scheduled_at' => now()->addDays(2)->startOfHour(),
        'status' => LeadScheduledEventStatus::Scheduled,
    ]);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Index Hover Lead')
        ->assertSee('leadStatusHover', false)
        ->assertSee('Index Hover Property · Index Dev', false)
        ->assertSee('Revisit', false);
});
