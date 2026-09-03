<?php

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadStatus;
use App\Models\Lead;

test('tenant sidebar shows activities menu with follow-ups and site visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Activities')
        ->assertSee('Follow-Ups')
        ->assertSee('Site Visits')
        ->assertSee(route('tenant.activities.index', ['tenant' => 'acme'], false));
});

test('follow-ups rescheduled stage shows rescheduled follow-ups', function () {
    createTestTenant();
    actingAsTenantUser();

    $rescheduledLead = Lead::factory()->create([
        'name' => 'Rescheduled Follow Up Lead',
        'next_follow_up_at' => now()->addDays(3),
    ]);
    scheduleFollowUpForLead($rescheduledLead, [
        'scheduled_at' => now()->addDays(3),
        'rescheduled_at' => now()->subHour(),
    ]);

    $pendingLead = Lead::factory()->create([
        'name' => 'Pending Follow Up Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($pendingLead, ['scheduled_at' => now()->addDay()]);

    $this->get('/acme/follow-ups?stage=rescheduled')
        ->assertOk()
        ->assertSee('Rescheduled Follow Up Lead')
        ->assertSee('Rescheduled Follow-Ups')
        ->assertDontSee('Pending Follow Up Lead');
});

test('follow-ups list defaults to pending stage', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Pending Follow Up Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($lead, ['scheduled_at' => now()->addDay()]);

    Lead::factory()->followUpDue()->create(['name' => 'Overdue Follow Up Lead']);

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee('Follow-Ups')
        ->assertSee('Upcoming')
        ->assertSee('Complete')
        ->assertSee('Overdue')
        ->assertSee('Reschedule')
        ->assertSee('Pending Follow Up Lead')
        ->assertSee('Initial Contact')
        ->assertDontSee('Overdue Follow Up Lead');
});

test('follow-ups overdue stage shows past due follow-ups', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->followUpDue()->create(['name' => 'Overdue Follow Up Lead']);

    $this->get('/acme/follow-ups?stage=overdue')
        ->assertOk()
        ->assertSee('Overdue Follow Up Lead')
        ->assertSee('Overdue Follow-Ups');
});

test('follow-ups completed stage shows completed follow-ups', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Completed Follow Up Lead',
        'next_follow_up_at' => now()->subDay(),
    ]);

    scheduleFollowUpForLead($lead, [
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subHours(2),
    ]);

    $this->get('/acme/follow-ups?stage=completed')
        ->assertOk()
        ->assertSee('Completed Follow Up Lead')
        ->assertSee('Complete Follow-Ups');
});

test('follow-ups list shows numbered labels for subsequent follow-ups', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Second Follow Up Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($lead, [
        'sequence_number' => 2,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee('2nd Follow-up');
});

test('follow-ups list excludes closed leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $openLead = Lead::factory()->create([
        'name' => 'Open Follow Up',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($openLead, ['scheduled_at' => now()->addDay()]);

    $convertedLead = Lead::factory()->create([
        'name' => 'Converted Follow Up',
        'status' => LeadStatus::Converted,
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($convertedLead, ['scheduled_at' => now()->addDay()]);

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee('Open Follow Up')
        ->assertDontSee('Converted Follow Up');
});

test('site visits rescheduled stage shows rescheduled visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $rescheduledLead = Lead::factory()->create([
        'name' => 'Rescheduled Site Visit Lead',
        'upcoming_site_visit_at' => now()->addDays(2),
    ]);
    scheduleSiteVisitForLead($rescheduledLead, [
        'scheduled_at' => now()->addDays(2),
        'rescheduled_at' => now()->subHour(),
    ]);

    $pendingLead = Lead::factory()->create([
        'name' => 'Pending Site Visit Lead',
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    scheduleSiteVisitForLead($pendingLead, ['scheduled_at' => now()->addDay()]);

    $this->get('/acme/site-visits?stage=rescheduled')
        ->assertOk()
        ->assertSee('Rescheduled Site Visit Lead')
        ->assertSee('Rescheduled Site Visits')
        ->assertDontSee('Pending Site Visit Lead');
});

test('site visits list defaults to pending stage', function () {
    createTestTenant();
    actingAsTenantUser();

    $pendingLead = Lead::factory()->create([
        'name' => 'Pending Site Visit Lead',
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    scheduleSiteVisitForLead($pendingLead, ['scheduled_at' => now()->addDay()]);

    $overdueLead = Lead::factory()->create([
        'name' => 'Overdue Site Visit Lead',
        'upcoming_site_visit_at' => now()->subDay(),
    ]);
    scheduleSiteVisitForLead($overdueLead, ['scheduled_at' => now()->subDay()]);

    $this->get('/acme/site-visits')
        ->assertOk()
        ->assertSee('Site Visits')
        ->assertSee('Pending Site Visit Lead')
        ->assertSee('1st Site Visit')
        ->assertDontSee('Overdue Site Visit Lead');
});

test('site visits overdue stage shows past due visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Overdue Site Visit Lead',
        'upcoming_site_visit_at' => now()->subDay(),
    ]);
    scheduleSiteVisitForLead($lead, ['scheduled_at' => now()->subDay()]);

    $this->get('/acme/site-visits?stage=overdue')
        ->assertOk()
        ->assertSee('Overdue Site Visit Lead')
        ->assertSee('Overdue Site Visits');
});

test('site visits completed stage shows completed visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Completed Site Visit Lead',
        'upcoming_site_visit_at' => now()->subDay(),
    ]);

    scheduleSiteVisitForLead($lead, [
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subHours(3),
    ]);

    $this->get('/acme/site-visits?stage=completed')
        ->assertOk()
        ->assertSee('Completed Site Visit Lead')
        ->assertSee('Complete Site Visits');
});

test('site visits list shows numbered labels for subsequent visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Second Site Visit Lead',
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    scheduleSiteVisitForLead($lead, [
        'sequence_number' => 2,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->get('/acme/site-visits')
        ->assertOk()
        ->assertSee('2nd Site Visit');
});

test('site visits list excludes closed leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $openLead = Lead::factory()->create([
        'name' => 'Open Site Visit',
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    scheduleSiteVisitForLead($openLead, ['scheduled_at' => now()->addDay()]);

    $lostLead = Lead::factory()->create([
        'name' => 'Lost Site Visit',
        'upcoming_site_visit_at' => now()->addDay(),
        'status' => LeadStatus::Lost,
    ]);
    scheduleSiteVisitForLead($lostLead, ['scheduled_at' => now()->addDay()]);

    $this->get('/acme/site-visits')
        ->assertOk()
        ->assertSee('Open Site Visit')
        ->assertDontSee('Lost Site Visit');
});
