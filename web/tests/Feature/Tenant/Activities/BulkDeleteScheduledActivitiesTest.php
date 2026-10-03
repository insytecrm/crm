<?php

use App\Enums\ActivityFilter;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Queries\ScheduledActivities;

test('bulk deleting scheduled activities clears lead dates so they are not backfilled', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->addDay(),
    ]);

    $event = LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => $lead->next_follow_up_at,
    ]);

    $this->from('/acme/activities')
        ->delete('/acme/table-bulk-delete/activities', [
            'event_ids' => [$event->id],
        ])
        ->assertRedirect('/acme/activities')
        ->assertSessionHas('status');

    expect(LeadScheduledEvent::query()->whereKey($event->id)->exists())->toBeFalse()
        ->and($lead->fresh()->next_follow_up_at)->toBeNull();

    app(ScheduledActivities::class)->items(ActivityFilter::All);

    expect(LeadScheduledEvent::query()->where('lead_id', $lead->id)->where('type', LeadScheduledEventType::FollowUp)->count())->toBe(0);
});

test('bulk deleting one of multiple follow-ups keeps the remaining scheduled date on the lead', function () {
    createTestTenant();
    actingAsTenantUser();

    $remainingAt = now()->addDays(3);
    $lead = Lead::factory()->create([
        'next_follow_up_at' => $remainingAt,
    ]);

    $older = LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 2,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => $remainingAt,
    ]);

    $this->from('/acme/follow-ups')
        ->delete('/acme/table-bulk-delete/follow_ups', [
            'event_ids' => [$older->id],
        ])
        ->assertRedirect('/acme/follow-ups');

    expect(LeadScheduledEvent::query()->whereKey($older->id)->exists())->toBeFalse()
        ->and($lead->fresh()->next_follow_up_at?->toDateTimeString())->toBe($remainingAt->toDateTimeString());
});
