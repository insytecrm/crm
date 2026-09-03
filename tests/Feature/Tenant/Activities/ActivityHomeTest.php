<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\SiteVisitNextStep;
use App\Enums\SiteVisitOutcome;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadScheduledEvent;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;

test('tenant users can view activities home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/activities')
        ->assertOk()
        ->assertSee('Activities')
        ->assertDontSee('Log Activity')
        ->assertDontSee('Filters', false)
        ->assertSee('Total Activities')
        ->assertSee('Site Visits')
        ->assertSee('Follow-ups')
        ->assertSee('All Activities')
        ->assertSee('Time')
        ->assertSee('Notes')
        ->assertSee('Actions');
});

test('activities home shows scheduled follow-ups by default', function () {
    createTestTenant();
    actingAsTenantUser();

    $tomorrowLead = Lead::factory()->create([
        'name' => 'Tomorrow Scheduled Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($tomorrowLead);

    $this->get('/acme/activities')
        ->assertOk()
        ->assertSee('Tomorrow Scheduled Lead');
});

test('activities home backfills missing scheduled events from lead follow-up dates', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Backfill Follow Up Lead',
        'next_follow_up_at' => now()->addHours(4),
    ]);

    expect($lead->scheduledEvents()->count())->toBe(0);

    $this->get('/acme/activities')
        ->assertOk()
        ->assertSee('Backfill Follow Up Lead');

    expect($lead->fresh()->scheduledEvents()->where('type', LeadScheduledEventType::FollowUp)->count())->toBe(1);
});

test('activities home shows property column for scheduled site visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Harbor Heights',
        'developer_name' => 'Acme Developers',
    ]);

    $lead = Lead::factory()->create([
        'name' => 'Site Visit Property Lead',
        'upcoming_site_visit_at' => now()->addHours(2),
    ]);

    scheduleSiteVisitForLead($lead, [
        'scheduled_at' => now()->addHours(2),
        'property_id' => $property->id,
    ]);

    $this->get('/acme/activities?filter=today&kind=site_visit')
        ->assertOk()
        ->assertSee('Property', false)
        ->assertSee('Harbor Heights · Acme Developers', false)
        ->assertSee('Site Visit Property Lead', false);
});

test('activities home does not show property column when all activities are listed', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Harbor Heights',
        'developer_name' => 'Acme Developers',
    ]);

    $lead = Lead::factory()->create([
        'name' => 'Mixed List Site Visit Lead',
        'upcoming_site_visit_at' => now()->addHours(2),
    ]);

    scheduleSiteVisitForLead($lead, [
        'scheduled_at' => now()->addHours(2),
        'property_id' => $property->id,
    ]);

    $response = $this->get('/acme/activities?filter=today');

    $response->assertOk()
        ->assertSee('Mixed List Site Visit Lead', false);

    expect($response->getContent())->not->toContain('>Property</th>');
});

test('activities home shows today follow-ups and site visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $followUpLead = Lead::factory()->create([
        'name' => 'Today Follow Up Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($followUpLead);

    $siteVisitLead = Lead::factory()->create([
        'name' => 'Today Site Visit Lead',
        'upcoming_site_visit_at' => now()->addHours(3),
    ]);
    scheduleSiteVisitForLead($siteVisitLead);

    $tomorrowLead = Lead::factory()->create([
        'name' => 'Tomorrow Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($tomorrowLead);

    $this->get('/acme/activities?filter=today')
        ->assertOk()
        ->assertSee('Today Follow Up Lead')
        ->assertSee('Today Site Visit Lead')
        ->assertDontSee('href="'.route('tenant.leads.index', ['tenant' => 'acme', 'lead' => $tomorrowLead->id], false).'"', false);
});

test('activities home shows complete follow-up modal fields', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Popup Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($lead);

    $this->get('/acme/activities?filter=today')
        ->assertOk()
        ->assertSee('Complete Follow-up', false)
        ->assertSee('Contact method', false)
        ->assertSee('What next?', false)
        ->assertSee('Initial Contact', false);
});

test('activities home shows numbered activity labels', function () {
    createTestTenant();
    actingAsTenantUser();

    $firstFollowUpLead = Lead::factory()->create([
        'name' => 'First Follow Up Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($firstFollowUpLead, ['sequence_number' => 1]);

    $secondFollowUpLead = Lead::factory()->create([
        'name' => 'Second Follow Up Lead',
        'next_follow_up_at' => now()->addHours(3),
    ]);
    scheduleFollowUpForLead($secondFollowUpLead, ['sequence_number' => 2]);

    $siteVisitLead = Lead::factory()->create([
        'name' => 'Site Visit Lead',
        'upcoming_site_visit_at' => now()->addHours(4),
    ]);
    scheduleSiteVisitForLead($siteVisitLead, ['sequence_number' => 1]);

    $this->get('/acme/activities?filter=today')
        ->assertOk()
        ->assertSee('Initial Contact')
        ->assertSee('2nd Follow-up')
        ->assertSee('1st Site Visit');
});

test('activities home shows scheduling notes for today items', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Notes Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);

    scheduleFollowUpForLead($lead, [
        'notes' => 'Discuss pricing options',
    ]);

    $this->get('/acme/activities?filter=today')
        ->assertOk()
        ->assertSee('Discuss pricing options');
});

test('tenant users can mark a follow-up complete from activities home', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Complete Me Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($lead);

    $this->from('/acme/activities?filter=today')
        ->post('/acme/leads/'.$lead->id.'/follow-up/complete')
        ->assertRedirect('/acme/activities?filter=today');

    $lead->refresh();

    expect($lead->next_follow_up_at)->toBeNull()
        ->and(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::FollowUpCompleted)->exists())->toBeTrue();
});

test('marking a follow-up complete stores optional completion notes', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Notes Complete Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($lead);

    $this->from('/acme/activities?filter=today')
        ->post('/acme/leads/'.$lead->id.'/follow-up/complete', [
            'notes' => 'Customer confirmed budget',
        ])
        ->assertRedirect('/acme/activities?filter=today');

    $activity = LeadActivity::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadActivityType::FollowUpCompleted)
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toContain('Customer confirmed budget')
        ->and($activity->metadata['completion_notes'] ?? null)->toBe('Customer confirmed budget');
});

test('tenant users can mark a site visit complete from activities home', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Visit Complete Lead',
        'status' => LeadStatus::Qualified,
        'upcoming_site_visit_at' => now()->addHours(2),
    ]);
    $event = scheduleSiteVisitForLead($lead, ['sequence_number' => 1]);

    $this->from('/acme/activities?filter=today')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'attended' => '1',
            'outcome' => SiteVisitOutcome::Interested->value,
            'next_step' => SiteVisitNextStep::None->value,
        ])
        ->assertRedirect('/acme/activities?filter=today');

    $lead->refresh();

    expect($lead->upcoming_site_visit_at)->toBeNull()
        ->and($lead->status)->toBe(LeadStatus::SiteVisit)
        ->and(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::SiteVisitCompleted)->exists())->toBeTrue();
});

test('completing a later site visit does not change lead status to site visit', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'upcoming_site_visit_at' => now()->addHours(2),
    ]);
    $event = scheduleSiteVisitForLead($lead, ['sequence_number' => 2]);

    $this->from('/acme/activities?filter=today')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'attended' => '1',
            'outcome' => SiteVisitOutcome::NotInterested->value,
            'next_step' => SiteVisitNextStep::None->value,
        ])
        ->assertRedirect('/acme/activities?filter=today');

    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified);
});

test('activities completed filter shows completed follow-ups and site visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $completedFollowUpLead = Lead::factory()->create(['name' => 'Completed Follow Up Lead']);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $completedFollowUpLead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => now()->subDay(),
    ]);

    $completedSiteVisitLead = Lead::factory()->create(['name' => 'Completed Site Visit Lead']);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $completedSiteVisitLead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'scheduled_at' => now()->subDay(),
    ]);

    $pendingLead = Lead::factory()->create([
        'name' => 'Pending Follow Up Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($pendingLead);

    $this->get('/acme/activities?filter=completed')
        ->assertOk()
        ->assertSee('Completed Activities')
        ->assertSee('Completed Follow Up Lead')
        ->assertSee('Completed Site Visit Lead')
        ->assertSee('Initial Contact')
        ->assertSee('1st Site Visit')
        ->assertDontSee('Pending Follow Up Lead');
});

test('activities all filter shows scheduled and completed activities', function () {
    createTestTenant();
    actingAsTenantUser();

    $pendingLead = Lead::factory()->create([
        'name' => 'Pending All Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($pendingLead);

    $completedLead = Lead::factory()->create(['name' => 'Completed All Lead']);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $completedLead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => now()->subDay(),
    ]);

    $this->get('/acme/activities?filter=all')
        ->assertOk()
        ->assertSee('All Activities')
        ->assertSee('Pending All Lead')
        ->assertSee('Completed All Lead');
});

test('activities statistics and list follow the selected tag', function () {
    createTestTenant();
    actingAsTenantUser();

    $todayLead = Lead::factory()->create([
        'name' => 'Today Scheduled Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($todayLead);

    $tomorrowLead = Lead::factory()->create([
        'name' => 'Tomorrow Scheduled Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($tomorrowLead);

    $completedLead = Lead::factory()->create(['name' => 'Completed Stats Lead']);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $completedLead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => now()->subDay(),
        'completed_at' => now()->subDay(),
    ]);

    $this->get('/acme/activities?filter=completed')
        ->assertOk()
        ->assertSee('Completed Stats Lead')
        ->assertDontSee('Today Scheduled Lead')
        ->assertDontSee('Tomorrow Scheduled Lead');

    $this->get('/acme/activities?filter=today')
        ->assertOk()
        ->assertSee('Today Scheduled Lead')
        ->assertDontSee('Tomorrow Scheduled Lead')
        ->assertDontSee('Completed Stats Lead');
});

test('activities cards filter the list by activity kind', function () {
    createTestTenant();
    actingAsTenantUser();

    $followUpLead = Lead::factory()->create([
        'name' => 'Kind Follow Up Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($followUpLead);

    $siteVisitLead = Lead::factory()->create([
        'name' => 'Kind Site Visit Lead',
        'upcoming_site_visit_at' => now()->addHours(3),
    ]);
    scheduleSiteVisitForLead($siteVisitLead);

    $this->get('/acme/activities?filter=today&kind=follow_up')
        ->assertOk()
        ->assertSee('Kind Follow Up Lead')
        ->assertDontSee('Kind Site Visit Lead');

    $this->get('/acme/activities?filter=today&kind=site_visit')
        ->assertOk()
        ->assertSee('Kind Site Visit Lead')
        ->assertDontSee('Kind Follow Up Lead');
});

test('activities overdue filter shows overdue scheduled items', function () {
    createTestTenant();
    actingAsTenantUser();

    $overdueLead = Lead::factory()->followUpDue()->create(['name' => 'Overdue Follow Up']);
    $futureLead = Lead::factory()->create([
        'name' => 'Future Follow Up',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($futureLead);

    $this->get('/acme/activities?filter=overdue')
        ->assertOk()
        ->assertSee('Overdue Follow Up')
        ->assertDontSee('href="'.route('tenant.leads.index', ['tenant' => 'acme', 'lead' => $futureLead->id], false).'"', false);
});

test('activities home does not error when completed events exist for inaccessible leads', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $adminLead = Lead::factory()->create([
        'name' => 'Admin Completed Lead',
        'assigned_to_id' => $admin->id,
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $adminLead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => now()->subDay(),
    ]);

    $agentLead = Lead::factory()->create([
        'name' => 'Agent Scheduled Lead',
        'assigned_to_id' => $agent->id,
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($agentLead);

    actingAsTenantUser($agent);

    $this->get('/acme/activities')
        ->assertOk()
        ->assertSee('Agent Scheduled Lead')
        ->assertDontSee('Admin Completed Lead');
});

test('clicking activities in sidebar opens activities home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee(route('tenant.activities.index', ['tenant' => 'acme'], false));
});
