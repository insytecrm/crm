<?php

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;

test('dashboard shows my day activities for today', function () {
    createTestTenant();
    actingAsTenantUser();

    $todayLead = Lead::factory()->create([
        'name' => 'My Day Follow Up Lead',
        'next_follow_up_at' => now()->addHours(2),
    ]);
    scheduleFollowUpForLead($todayLead, [
        'scheduled_at' => now()->addHours(2),
    ]);

    $siteVisitLead = Lead::factory()->create([
        'name' => 'My Day Site Visit Lead',
        'upcoming_site_visit_at' => now()->addHours(3),
    ]);
    scheduleSiteVisitForLead($siteVisitLead, [
        'scheduled_at' => now()->addHours(3),
    ]);

    $tomorrowLead = Lead::factory()->create([
        'name' => 'Tomorrow Only Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($tomorrowLead, [
        'scheduled_at' => now()->addDay(),
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('My Day')
        ->assertSee('Follow-ups')
        ->assertSee('Site Visits')
        ->assertSee('My Day Follow Up Lead')
        ->assertSee('My Day Site Visit Lead')
        ->assertDontSee('Tomorrow Only Lead')
        ->assertViewHas('myDayActivities', function ($activities) use ($todayLead, $siteVisitLead): bool {
            $names = $activities->map(fn (array $item): string => $item['lead']->name)->all();

            return in_array('My Day Follow Up Lead', $names, true)
                && in_array('My Day Site Visit Lead', $names, true)
                && ! in_array('Tomorrow Only Lead', $names, true)
                && $activities->contains(fn (array $item): bool => $item['lead']->is($todayLead))
                && $activities->contains(fn (array $item): bool => $item['lead']->is($siteVisitLead))
                && $activities->where('kind', 'follow_up')->count() === 1
                && $activities->where('kind', 'site_visit')->count() === 1;
        });
});

test('dashboard my day includes overdue activities and complete actions', function () {
    createTestTenant();
    actingAsTenantUser();

    $overdueLead = Lead::factory()->create([
        'name' => 'Overdue Follow Up Lead',
        'next_follow_up_at' => now()->subDay(),
    ]);
    $event = scheduleFollowUpForLead($overdueLead, [
        'scheduled_at' => now()->subDay(),
        'status' => LeadScheduledEventStatus::Scheduled,
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Overdue Follow Up Lead')
        ->assertSee('complete-follow-up-'.$event->id, false)
        ->assertSee(route('tenant.scheduled-events.complete-follow-up', $event, false), false);
});

test('dashboard my day excludes completed activities', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Already Done Lead',
        'next_follow_up_at' => null,
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => now()->subHour(),
        'completed_at' => now(),
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('No activities for today.')
        ->assertDontSee('Already Done Lead');
});
