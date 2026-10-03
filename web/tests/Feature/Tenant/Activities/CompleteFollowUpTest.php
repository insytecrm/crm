<?php

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityNextStep;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\Property;

test('follow-ups list shows complete follow-up modal trigger', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Complete Modal Lead',
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($lead, ['scheduled_at' => now()->addDay()]);

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee('Complete Modal Lead')
        ->assertSee('Complete Follow-up', false)
        ->assertSee('title="Complete"', false);
});

test('user can complete follow-up with outcome and schedule site visit next', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->addDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->addDay(),
        'priority' => ScheduledActivityPriority::High,
    ]);

    $property = Property::factory()->create();
    $siteVisitDate = now()->addDays(3)->startOfSecond();
    $siteVisitDateString = $siteVisitDate->format('Y-m-d H:i:s');

    $this->from('/acme/follow-ups')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
            'contact_method' => ScheduledActivityContactMethod::WhatsApp->value,
            'outcome' => ScheduledActivityOutcome::Interested->value,
            'notes' => 'Wants a site visit',
            'next_step' => ScheduledActivityNextStep::ScheduleSiteVisit->value,
            'next_property_id' => $property->id,
            'next_visit_type' => SiteVisitType::FreshVisit->value,
            'next_scheduled_at' => $siteVisitDateString,
            'next_notes' => 'Weekend visit',
        ])
        ->assertRedirect('/acme/follow-ups')
        ->assertSessionHas('status');

    $event->refresh();
    $lead->refresh();

    expect($event->status)->toBe(LeadScheduledEventStatus::Completed)
        ->and($event->completion_method)->toBe(ScheduledActivityContactMethod::WhatsApp)
        ->and($event->completion_outcome)->toBe(ScheduledActivityOutcome::Interested)
        ->and($event->next_step_type)->toBe(ScheduledActivityNextStep::ScheduleSiteVisit)
        ->and($event->completion_notes)->toBe('Wants a site visit')
        ->and($lead->status)->toBe(LeadStatus::Qualified)
        ->and($lead->next_follow_up_at)->toBeNull()
        ->and($lead->upcoming_site_visit_at->format('Y-m-d H:i:s'))->toBe($siteVisitDateString);

    expect(LeadScheduledEvent::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadScheduledEventType::SiteVisit)
        ->where('status', LeadScheduledEventStatus::Scheduled)
        ->where('property_id', $property->id)
        ->where('visit_type', SiteVisitType::FreshVisit)
        ->exists())->toBeTrue();
});

test('user can complete follow-up and schedule another follow-up with priority', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->subHour(),
    ]);
    $event = scheduleFollowUpForLead($lead, ['scheduled_at' => now()->subHour()]);

    $nextDate = now()->addDays(2)->startOfSecond();
    $nextDateString = $nextDate->format('Y-m-d H:i:s');

    $this->from('/acme/follow-ups?stage=overdue')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
            'contact_method' => ScheduledActivityContactMethod::Call->value,
            'outcome' => ScheduledActivityOutcome::CallbackRequested->value,
            'next_step' => ScheduledActivityNextStep::ScheduleFollowUp->value,
            'next_scheduled_at' => $nextDateString,
            'next_priority' => ScheduledActivityPriority::High->value,
        ])
        ->assertRedirect('/acme/follow-ups?stage=overdue');

    $lead->refresh();

    $newEvent = LeadScheduledEvent::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadScheduledEventType::FollowUp)
        ->where('status', LeadScheduledEventStatus::Scheduled)
        ->first();

    expect($lead->status)->toBe(LeadStatus::Contacted);

    expect($newEvent)->not->toBeNull()
        ->and($newEvent->priority)->toBe(ScheduledActivityPriority::High)
        ->and($newEvent->scheduled_at->format('Y-m-d H:i:s'))->toBe($nextDateString);
});

test('completing the first follow-up marks a new lead as contacted', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::New,
        'next_follow_up_at' => now()->addDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, [
        'sequence_number' => 1,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->from('/acme/follow-ups')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
            'contact_method' => ScheduledActivityContactMethod::Call->value,
            'outcome' => ScheduledActivityOutcome::Connected->value,
            'next_step' => ScheduledActivityNextStep::None->value,
        ])
        ->assertRedirect('/acme/follow-ups');

    expect($lead->fresh()->status)->toBe(LeadStatus::Contacted);
});

test('completing the first follow-up as interested marks the lead as qualified', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::New,
        'next_follow_up_at' => now()->addDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, [
        'sequence_number' => 1,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->from('/acme/follow-ups')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
            'contact_method' => ScheduledActivityContactMethod::Call->value,
            'outcome' => ScheduledActivityOutcome::Interested->value,
            'next_step' => ScheduledActivityNextStep::None->value,
        ])
        ->assertRedirect('/acme/follow-ups');

    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified);
});

test('completing the second follow-up marks the lead as follow-up', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'next_follow_up_at' => now()->addDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, [
        'sequence_number' => 2,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->from('/acme/follow-ups')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
            'contact_method' => ScheduledActivityContactMethod::Call->value,
            'outcome' => ScheduledActivityOutcome::Interested->value,
            'next_step' => ScheduledActivityNextStep::None->value,
        ])
        ->assertRedirect('/acme/follow-ups');

    expect($lead->fresh()->status)->toBe(LeadStatus::FollowUp);
});

test('completing a third follow-up does not change lead status to follow-up', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'next_follow_up_at' => now()->addDay(),
    ]);
    $event = scheduleFollowUpForLead($lead, [
        'sequence_number' => 3,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->from('/acme/follow-ups')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
            'contact_method' => ScheduledActivityContactMethod::Call->value,
            'outcome' => ScheduledActivityOutcome::Interested->value,
            'next_step' => ScheduledActivityNextStep::None->value,
        ])
        ->assertRedirect('/acme/follow-ups');

    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified);
});

test('scheduling follow-up requires priority', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();

    $this->from('/acme/leads')
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])
        ->assertSessionHasErrors('priority');
});
