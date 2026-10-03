<?php

use App\Actions\RecalculateLeadScore;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityNextStep;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\SiteVisitOutcome;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Support\LeadScoring;

test('lead score increases from positive follow-up outcomes', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'lead_score' => 0,
        'lead_score_intent' => 0,
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subDay(),
        'completion_outcome' => ScheduledActivityOutcome::Interested->value,
    ]);

    app(RecalculateLeadScore::class)->handle($lead);
    $lead->refresh();

    expect($lead->lead_score_intent)->toBe(25)
        ->and($lead->lead_score)->toBeGreaterThan(0)
        ->and($lead->latest_positive_outcome)->toBe(ScheduledActivityOutcome::Interested->value)
        ->and($lead->qualifiesForPriority())->toBeTrue();
});

test('ready to visit and ready to book outcomes rank higher than interested', function () {
    createTestTenant();
    actingAsTenantUser();

    $visitLead = Lead::factory()->create(['status' => LeadStatus::Qualified]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $visitLead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now(),
        'completion_outcome' => ScheduledActivityOutcome::ReadyToVisit->value,
    ]);

    $bookLead = Lead::factory()->create(['status' => LeadStatus::SiteVisit]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $bookLead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now(),
        'attended' => true,
        'completion_outcome' => SiteVisitOutcome::ReadyToBook->value,
    ]);

    app(RecalculateLeadScore::class)->handle($visitLead);
    app(RecalculateLeadScore::class)->handle($bookLead);

    $visitLead->refresh();
    $bookLead->refresh();

    expect($bookLead->lead_score_intent)->toBeGreaterThan($visitLead->lead_score_intent)
        ->and($visitLead->lead_score_intent)->toBe(60);
});

test('latest negative outcome removes lead from priority eligibility', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Qualified]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subDays(2),
        'completion_outcome' => ScheduledActivityOutcome::Interested->value,
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 2,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subDay(),
        'completion_outcome' => ScheduledActivityOutcome::NotInterested->value,
    ]);

    app(RecalculateLeadScore::class)->handle($lead);
    $lead->refresh();

    expect($lead->lead_score_intent)->toBe(0)
        ->and($lead->latest_positive_outcome_at)->toBeNull()
        ->and($lead->qualifiesForPriority())->toBeFalse();
});

test('priority leads list shows only leads with positive intent outcomes', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->priority()->create(['name' => 'Priority Lead']);

    Lead::factory()->create([
        'name' => 'Regular Lead',
        'lead_score' => 0,
        'lead_score_intent' => 0,
    ]);

    $this->get('/acme/leads/priority')
        ->assertOk()
        ->assertSee('Priority Lead')
        ->assertDontSee('Regular Lead');
});

test('priority leads list sorts by intent score descending', function () {
    createTestTenant();
    actingAsTenantUser();

    $interestedLead = Lead::factory()->create(['name' => 'Interested Lead', 'status' => LeadStatus::Qualified]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $interestedLead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now(),
        'completion_outcome' => ScheduledActivityOutcome::Interested->value,
    ]);

    $readyLead = Lead::factory()->create(['name' => 'Ready Lead', 'status' => LeadStatus::SiteVisit]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $readyLead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now(),
        'attended' => true,
        'completion_outcome' => SiteVisitOutcome::ReadyToBook->value,
    ]);

    app(RecalculateLeadScore::class)->handle($interestedLead);
    app(RecalculateLeadScore::class)->handle($readyLead);

    $response = $this->get('/acme/leads/priority')->assertOk();

    expect($response->content())->toMatch('/Ready Lead[\s\S]*Interested Lead/');
});

test('completing follow-up recalculates lead score automatically', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->addDay(),
        'lead_score' => 0,
        'lead_score_intent' => 0,
    ]);

    $event = scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->addDay(),
    ]);

    $this->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
        'contact_method' => ScheduledActivityContactMethod::Call->value,
        'outcome' => ScheduledActivityOutcome::ReadyToVisit->value,
        'next_step' => ScheduledActivityNextStep::None->value,
    ])->assertRedirect();

    $lead->refresh();

    expect($lead->lead_score_intent)->toBe(60)
        ->and($lead->latest_positive_outcome)->toBe(ScheduledActivityOutcome::ReadyToVisit->value)
        ->and($lead->qualifiesForPriority())->toBeTrue();
});

test('ready to visit appears in follow-up outcome options', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['next_follow_up_at' => now()->addDay()]);
    scheduleFollowUpForLead($lead, ['scheduled_at' => now()->addDay()]);

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee(ScheduledActivityOutcome::ReadyToVisit->label(), false);
});

test('lead score is capped at 100', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now(),
        'attended' => true,
        'completion_outcome' => SiteVisitOutcome::ReadyToBook->value,
    ]);

    app(RecalculateLeadScore::class)->handle($lead);
    $lead->refresh();

    expect($lead->lead_score)->toBeLessThanOrEqual(100);
});

test('recalculate scores command updates all leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['lead_score' => 0, 'lead_score_intent' => 0]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now(),
        'completion_outcome' => ScheduledActivityOutcome::CallbackRequested->value,
    ]);

    $this->artisan('leads:recalculate-scores')->assertSuccessful();

    $lead->refresh();

    expect($lead->lead_score_intent)->toBe(40);
});

test('lead scoring constants define positive follow-up outcomes', function () {
    expect(LeadScoring::followUpIntentPoints())->toHaveKeys([
        ScheduledActivityOutcome::Interested->value,
        ScheduledActivityOutcome::CallbackRequested->value,
        ScheduledActivityOutcome::ReadyToVisit->value,
    ]);
});
