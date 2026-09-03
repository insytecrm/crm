<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Models\Lead;
use App\Models\LeadActivity;

test('follow-ups list shows reschedule action for pending activities', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Reschedule Follow Up Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($lead, ['scheduled_at' => now()->addDay()]);

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee('Reschedule Follow Up Lead')
        ->assertSee('title="Reschedule"', false)
        ->assertSee('Reschedule Follow-up', false);
});

test('site visits list shows reschedule action for overdue activities', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Reschedule Site Visit Lead',
        'upcoming_site_visit_at' => now()->subDay(),
    ]);
    scheduleSiteVisitForLead($lead, ['scheduled_at' => now()->subDay()]);

    $this->get('/acme/site-visits?stage=overdue')
        ->assertOk()
        ->assertSee('Reschedule Site Visit Lead')
        ->assertSee('title="Reschedule"', false)
        ->assertSee('Reschedule Site Visit', false);
});

test('user can reschedule a follow-up from the activities list', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->addDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, ['scheduled_at' => now()->addDay()]);
    $newDate = now()->addDays(3)->startOfSecond();
    $newDateString = $newDate->format('Y-m-d H:i:s');

    $this->from('/acme/follow-ups')
        ->patch('/acme/scheduled-events/'.$event->id.'/reschedule', [
            'scheduled_at' => $newDateString,
            'notes' => 'Moved to next week',
        ])
        ->assertRedirect('/acme/follow-ups')
        ->assertSessionHas('status');

    $event->refresh();
    $lead->refresh();

    expect($event->scheduled_at->format('Y-m-d H:i:s'))->toBe($newDateString)
        ->and($event->notes)->toBe('Moved to next week')
        ->and($event->rescheduled_at)->not->toBeNull()
        ->and($lead->next_follow_up_at->format('Y-m-d H:i:s'))->toBe($newDateString)
        ->and(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::FollowUpScheduled)->count())->toBe(1);
});

test('user can reschedule a site visit and sync lead upcoming date', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'upcoming_site_visit_at' => now()->subDay(),
    ]);
    $event = scheduleSiteVisitForLead($lead, ['scheduled_at' => now()->subDay()]);
    $newDateString = now()->addDays(2)->startOfSecond()->format('Y-m-d H:i:s');

    $this->from('/acme/site-visits?stage=overdue')
        ->patch('/acme/scheduled-events/'.$event->id.'/reschedule', [
            'scheduled_at' => $newDateString,
        ])
        ->assertRedirect('/acme/site-visits?stage=overdue');

    $event->refresh();
    $lead->refresh();

    expect($event->scheduled_at->format('Y-m-d H:i:s'))->toBe($newDateString)
        ->and($lead->upcoming_site_visit_at->format('Y-m-d H:i:s'))->toBe($newDateString);
});

test('rescheduling an older follow-up does not overwrite lead next follow-up date', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->addDays(5),
    ]);

    $firstEvent = scheduleFollowUpForLead($lead, [
        'sequence_number' => 1,
        'scheduled_at' => now()->addDay(),
    ]);
    $latestDate = now()->addDays(5);
    scheduleFollowUpForLead($lead, [
        'sequence_number' => 2,
        'scheduled_at' => $latestDate,
    ]);

    $latestDateString = $latestDate->format('Y-m-d H:i:s');
    $rescheduledDateString = now()->addDays(2)->startOfSecond()->format('Y-m-d H:i:s');

    $this->from('/acme/follow-ups')
        ->patch('/acme/scheduled-events/'.$firstEvent->id.'/reschedule', [
            'scheduled_at' => $rescheduledDateString,
        ])
        ->assertRedirect('/acme/follow-ups');

    $lead->refresh();

    expect($lead->next_follow_up_at->format('Y-m-d H:i:s'))->toBe($latestDateString);
});

test('completed scheduled events cannot be rescheduled', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->subDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, [
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subHour(),
        'scheduled_at' => now()->subDay(),
    ]);
    $originalScheduledAt = $event->scheduled_at->format('Y-m-d H:i:s');

    $this->from('/acme/follow-ups?stage=completed')
        ->patch('/acme/scheduled-events/'.$event->id.'/reschedule', [
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])
        ->assertRedirect('/acme/follow-ups?stage=completed')
        ->assertSessionHas('status');

    expect($event->fresh()->scheduled_at->format('Y-m-d H:i:s'))->toBe($originalScheduledAt);
});

test('reschedule requires a valid scheduled date', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->addDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, ['scheduled_at' => now()->addDay()]);

    $this->from('/acme/follow-ups')
        ->patch('/acme/scheduled-events/'.$event->id.'/reschedule', [
            'scheduled_at' => '',
        ])
        ->assertSessionHasErrors('scheduled_at');
});
