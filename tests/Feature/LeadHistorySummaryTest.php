<?php

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\SiteVisitType;
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
        ->and($summary['insight'])->toContain('Revisit');

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
        ->assertSee('AI Insight')
        ->assertSee('Drawer History Lead')
        ->assertSee('Follow-ups completed')
        ->assertSee('Site visits completed')
        ->assertSee('Projects visited')
        ->assertSee('History Heights · History Developers')
        ->assertSee('Fresh Visit')
        ->assertSee('Revisit');
});
