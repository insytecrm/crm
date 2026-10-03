<?php

use App\Enums\LeadClosingReason;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\SiteVisitType;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\Property;
use App\Support\LeadHistorySummary;

test('lead history summary builds insight stats and projects visited', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'History Lead',
        'source' => 'Facebook',
        'status' => LeadStatus::SiteVisit,
    ]);
    $firstProperty = Property::factory()->create([
        'project_name' => 'Alpha Towers',
        'developer_name' => 'Alpha Developers',
    ]);
    $secondProperty = Property::factory()->create([
        'project_name' => 'Beta Homes',
        'developer_name' => 'Beta Developers',
    ]);

    scheduleFollowUpForLead($lead, [
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subDays(3),
    ]);
    scheduleFollowUpForLead($lead, [
        'sequence_number' => 2,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subDays(2),
    ]);
    scheduleSiteVisitForLead($lead, [
        'sequence_number' => 1,
        'property_id' => $firstProperty->id,
        'visit_type' => SiteVisitType::FreshVisit,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subDay(),
    ]);
    scheduleSiteVisitForLead($lead, [
        'sequence_number' => 2,
        'property_id' => $firstProperty->id,
        'visit_type' => SiteVisitType::Revisit,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subHours(2),
    ]);
    scheduleSiteVisitForLead($lead, [
        'sequence_number' => 3,
        'property_id' => $secondProperty->id,
        'visit_type' => SiteVisitType::FreshVisit,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subHour(),
    ]);
    scheduleSiteVisitForLead($lead, [
        'sequence_number' => 4,
        'property_id' => $secondProperty->id,
        'visit_type' => SiteVisitType::FreshVisit,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);

    $summary = app(LeadHistorySummary::class)->for($lead->load('scheduledEvents.property'));

    expect($summary['follow_ups_completed'])->toBe(2)
        ->and($summary['site_visits_completed'])->toBe(3)
        ->and($summary['projects'])->toHaveCount(2)
        ->and($summary['insight'])->toContain('History Lead')
        ->and($summary['insight'])->toContain('Facebook')
        ->and($summary['insight'])->toContain('Site Visit')
        ->and($summary['insight'])->toContain('2 follow-ups have been completed')
        ->and($summary['insight'])->toContain('Alpha Towers')
        ->and($summary['insight'])->toContain('Fresh Visit')
        ->and($summary['insight'])->toContain('Revisit')
        ->and(collect($summary['journey'])->pluck('key'))->toContain('created', 'follow_up', 'site_visit', 'booking', 'payout')
        ->and(collect($summary['journey'])->firstWhere('key', 'follow_up')['state'])->toBe('completed')
        ->and(collect($summary['journey'])->firstWhere('key', 'site_visit')['state'])->toBe('completed')
        ->and(collect($summary['journey'])->firstWhere('key', 'booking')['state'])->toBe('upcoming');

    $alpha = collect($summary['projects'])->firstWhere('label', 'Alpha Towers · Alpha Developers');
    $beta = collect($summary['projects'])->firstWhere('label', 'Beta Homes · Beta Developers');

    expect($alpha)->not->toBeNull()
        ->and($alpha['fresh_visits'])->toBe(1)
        ->and($alpha['revisits'])->toBe(1)
        ->and($alpha['total_visits'])->toBe(2)
        ->and($beta['fresh_visits'])->toBe(1)
        ->and($beta['revisits'])->toBe(0)
        ->and($beta['total_visits'])->toBe(1);
});

test('lead drawer history tab shows ai insight and project visit counts', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Drawer History Lead',
        'source' => 'Website',
        'status' => LeadStatus::Qualified,
    ]);
    $property = Property::factory()->create([
        'project_name' => 'History Heights',
        'developer_name' => 'History Developers',
    ]);

    scheduleFollowUpForLead($lead, [
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subDay(),
    ]);
    scheduleSiteVisitForLead($lead, [
        'property_id' => $property->id,
        'visit_type' => SiteVisitType::FreshVisit,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subHours(3),
        'type' => LeadScheduledEventType::SiteVisit,
    ]);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('History')
        ->assertSee('Lead journey')
        ->assertSee('Lead created')
        ->assertSee('Payout received')
        ->assertSee('AI Insight')
        ->assertSee('Drawer History Lead')
        ->assertSee('Follow-ups completed')
        ->assertSee('Site visits completed')
        ->assertSee('Projects visited')
        ->assertSee('History Heights · History Developers')
        ->assertSee('Fresh Visit')
        ->assertSee('Revisit');
});

test('lead history journey marks booking through payout as completed', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Converted Journey Lead',
        'status' => LeadStatus::Converted,
        'closing_reason' => LeadClosingReason::Converted,
    ]);
    $property = Property::factory()->create(['project_name' => 'Summit Park']);
    Booking::factory()->create([
        'lead_id' => $lead->id,
        'property_id' => $property->id,
        'unit_number' => '1201',
        'booking_date' => '2026-08-01',
        'agreement_date' => '2026-08-10',
        'invoice_date' => '2026-08-15',
        'invoice_number' => 'INV-00042',
        'invoiced_at' => now(),
        'payout_paid_at' => now(),
    ]);

    $summary = app(LeadHistorySummary::class)->for($lead->load('latestBooking.property', 'scheduledEvents.property'));
    $steps = collect($summary['journey'])->keyBy('key');

    expect($steps['booking']['state'])->toBe('completed')
        ->and($steps['booking']['detail'])->toContain('Summit Park')
        ->and($steps['agreement']['state'])->toBe('completed')
        ->and($steps['invoice']['state'])->toBe('completed')
        ->and($steps['payout']['state'])->toBe('completed')
        ->and($summary['insight'])->toContain('Payout has been received');
});

test('lost lead history journey includes lost and omits unpaid commercial steps', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::Lost,
        'closing_reason' => LeadClosingReason::NotInterested,
        'closed_at' => now(),
    ]);

    $summary = app(LeadHistorySummary::class)->for($lead->load('latestBooking', 'scheduledEvents.property', 'activities'));
    $keys = collect($summary['journey'])->pluck('key');

    expect($keys)->toContain('created', 'lost')
        ->and($keys)->not->toContain('booking', 'payout')
        ->and(collect($summary['journey'])->firstWhere('key', 'lost')['state'])->toBe('completed')
        ->and(collect($summary['journey'])->firstWhere('key', 'contacted')['state'])->toBe('skipped');
});
