<?php

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\SiteVisitOutcome;
use App\Enums\SiteVisitType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\Property;
use App\Support\ResolveMessageTemplateValues;

test('resolver returns separate date and time for upcoming site visit', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'upcoming_site_visit_at' => '2026-09-14 11:00:00',
    ]);

    $property = Property::factory()->create([
        'project_name' => 'Riverfront Residences',
        'developer_name' => null,
    ]);

    scheduleSiteVisitForLead($lead, [
        'property_id' => $property->id,
        'visit_type' => SiteVisitType::FreshVisit,
        'scheduled_at' => '2026-09-14 11:00:00',
        'sequence_number' => 1,
    ]);

    $values = app(ResolveMessageTemplateValues::class)->handle($lead);

    expect($values['site_visit.upcoming.date'])->toBe('14 Sep 2026')
        ->and($values['site_visit.upcoming.time'])->toBe('11:00 AM')
        ->and($values['site_visit.upcoming.property'])->toBe('Riverfront Residences')
        ->and($values['site_visit.upcoming.visit_type'])->toBe('Fresh Visit')
        ->and($values['lead.upcoming_site_visit_date'])->toBe('14 Sep 2026')
        ->and($values['lead.upcoming_site_visit_time'])->toBe('11:00 AM');
});

test('resolver returns previous and completed site visit details separately', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();

    $property = Property::factory()->create([
        'project_name' => 'Skyline Towers',
        'developer_name' => null,
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'property_id' => $property->id,
        'visit_type' => SiteVisitType::FreshVisit,
        'sequence_number' => 1,
        'scheduled_at' => '2026-09-01 10:00:00',
        'completed_at' => '2026-09-01 10:30:00',
        'attended' => true,
        'completion_outcome' => SiteVisitOutcome::Interested->value,
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'property_id' => $property->id,
        'visit_type' => SiteVisitType::Revisit,
        'sequence_number' => 2,
        'scheduled_at' => '2026-09-08 15:00:00',
        'completed_at' => '2026-09-08 15:15:00',
        'attended' => true,
        'completion_outcome' => SiteVisitOutcome::ReadyToBook->value,
    ]);

    $values = app(ResolveMessageTemplateValues::class)->handle($lead->fresh());

    expect($values['site_visit.completed.date'])->toBe('8 Sep 2026')
        ->and($values['site_visit.completed.time'])->toBe('3:15 PM')
        ->and($values['site_visit.completed.visit_type'])->toBe('Revisit')
        ->and($values['site_visit.previous.date'])->toBe('1 Sep 2026')
        ->and($values['site_visit.previous.time'])->toBe('10:30 AM')
        ->and($values['site_visit.previous.visit_type'])->toBe('Fresh Visit');
});

test('resolver returns follow-up upcoming previous and completed values', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => '2026-09-12 16:00:00',
    ]);

    scheduleFollowUpForLead($lead, [
        'scheduled_at' => '2026-09-12 16:00:00',
        'sequence_number' => 3,
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => '2026-09-01 11:00:00',
        'completed_at' => '2026-09-01 11:20:00',
        'completion_method' => ScheduledActivityContactMethod::Call->value,
        'completion_outcome' => ScheduledActivityOutcome::Connected->value,
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 2,
        'scheduled_at' => '2026-09-05 14:00:00',
        'completed_at' => '2026-09-05 14:10:00',
        'completion_method' => ScheduledActivityContactMethod::WhatsApp->value,
        'completion_outcome' => ScheduledActivityOutcome::Interested->value,
    ]);

    $values = app(ResolveMessageTemplateValues::class)->handle($lead->fresh());

    expect($values['follow_up.upcoming.date'])->toBe('12 Sep 2026')
        ->and($values['follow_up.upcoming.time'])->toBe('4:00 PM')
        ->and($values['follow_up.completed.method'])->toBe('WhatsApp')
        ->and($values['follow_up.previous.method'])->toBe('Call')
        ->and($values['lead.next_follow_up_date'])->toBe('12 Sep 2026')
        ->and($values['lead.next_follow_up_time'])->toBe('4:00 PM');
});

test('resolver marks overdue upcoming activities', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->subHour(),
    ]);

    scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->subHour(),
        'status' => LeadScheduledEventStatus::Scheduled,
    ]);

    $values = app(ResolveMessageTemplateValues::class)->handle($lead);

    expect($values['follow_up.upcoming.state'])->toBe('Overdue');
});
