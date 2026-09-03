<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadStatus;
use App\Enums\SiteVisitNextStep;
use App\Enums\SiteVisitOutcome;
use App\Enums\SiteVisitType;
use App\Models\Lead;
use App\Models\LeadActivity;

test('site visits list shows complete site visit modal trigger', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Site Visit Modal Lead',
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    scheduleSiteVisitForLead($lead, [
        'scheduled_at' => now()->addDay(),
        'visit_type' => SiteVisitType::FreshVisit,
    ]);

    $this->get('/acme/site-visits')
        ->assertOk()
        ->assertSee('Site Visit Modal Lead')
        ->assertSee('Complete Site Visit', false)
        ->assertSee('Attended', false)
        ->assertSee('Ready to book', false)
        ->assertSee('Create booking', false);
});

test('user can complete site visit with attended outcome and next follow-up', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    $event = scheduleSiteVisitForLead($lead, [
        'sequence_number' => 1,
        'scheduled_at' => now()->addDay(),
        'visit_type' => SiteVisitType::FreshVisit,
    ]);

    $nextDate = now()->addDays(2)->startOfSecond();
    $nextDateString = $nextDate->format('Y-m-d H:i:s');

    $this->from('/acme/site-visits')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'attended' => '1',
            'outcome' => SiteVisitOutcome::Interested->value,
            'notes' => 'Liked the amenities',
            'next_step' => SiteVisitNextStep::ScheduleFollowUp->value,
            'next_scheduled_at' => $nextDateString,
            'next_priority' => 'normal',
            'next_notes' => 'Send brochure',
        ])
        ->assertRedirect('/acme/site-visits')
        ->assertSessionHas('status');

    $event->refresh();
    $lead->refresh();

    expect($event->status)->toBe(LeadScheduledEventStatus::Completed)
        ->and($event->attended)->toBeTrue()
        ->and($event->completion_outcome)->toBe(SiteVisitOutcome::Interested)
        ->and($event->next_step_type)->toBe(SiteVisitNextStep::ScheduleFollowUp)
        ->and($event->completion_notes)->toBe('Liked the amenities')
        ->and($lead->status)->toBe(LeadStatus::SiteVisit)
        ->and($lead->upcoming_site_visit_at)->toBeNull()
        ->and($lead->next_follow_up_at?->format('Y-m-d H:i:s'))->toBe($nextDateString);

    expect(LeadActivity::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadActivityType::SiteVisitCompleted)
        ->exists())->toBeTrue();
});

test('user can complete site visit and choose create booking next', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'upcoming_site_visit_at' => now()->subHour(),
    ]);
    $event = scheduleSiteVisitForLead($lead, [
        'scheduled_at' => now()->subHour(),
        'visit_type' => SiteVisitType::Revisit,
    ]);

    $this->from('/acme/site-visits?stage=overdue')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'attended' => true,
            'outcome' => SiteVisitOutcome::ReadyToBook->value,
            'next_step' => SiteVisitNextStep::CreateBooking->value,
        ])
        ->assertRedirect();

    expect($event->fresh()->completion_outcome)->toBe(SiteVisitOutcome::ReadyToBook)
        ->and($event->fresh()->next_step_type)->toBe(SiteVisitNextStep::CreateBooking)
        ->and($lead->fresh()->next_action)->toBe('Create Booking');
});

test('completing site visit requires attended and requires outcome only when attended', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    $event = scheduleSiteVisitForLead($lead);

    $this->from('/acme/site-visits')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'next_step' => SiteVisitNextStep::None->value,
        ])
        ->assertSessionHasErrors(['attended']);

    $this->from('/acme/site-visits')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'attended' => true,
            'next_step' => SiteVisitNextStep::None->value,
        ])
        ->assertSessionHasErrors(['outcome']);
});

test('completing site visit without attendance does not require outcome', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Visit Complete Lead',
        'status' => LeadStatus::Qualified,
        'upcoming_site_visit_at' => now()->addHours(2),
    ]);
    $event = scheduleSiteVisitForLead($lead, [
        'sequence_number' => 1,
        'visit_type' => SiteVisitType::FreshVisit,
    ]);

    $this->from('/acme/activities?filter=today')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'attended' => false,
            'next_step' => SiteVisitNextStep::None->value,
            'notes' => 'Reschedule later',
        ])
        ->assertRedirect('/acme/activities?filter=today');

    $lead->refresh();
    $event->refresh();

    expect($lead->upcoming_site_visit_at)->toBeNull()
        ->and($lead->status)->toBe(LeadStatus::SiteVisit)
        ->and($event->attended)->toBeFalse()
        ->and($event->completion_outcome)->toBeNull()
        ->and(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::SiteVisitCompleted)->exists())->toBeTrue();
});
