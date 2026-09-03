<?php

use App\Enums\LeadScheduledEventType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\Property;

test('scheduling a follow-up with reminder stores remind_at', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Reminder Follow Up Lead',
        'assigned_to_id' => $user->id,
    ]);

    $scheduledAt = now()->addHours(2);

    $this->post('/acme/leads/'.$lead->id.'/follow-up', [
        'next_follow_up_at' => $scheduledAt->format('Y-m-d H:i:s'),
        'priority' => 'normal',
        'notes' => 'Call back',
        'add_reminder' => 1,
        'reminder_hours' => 0,
        'reminder_minutes' => 30,
        'reminder_seconds' => 0,
    ])->assertRedirect();

    $event = LeadScheduledEvent::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadScheduledEventType::FollowUp)
        ->latest('id')
        ->first();

    expect($event)->not->toBeNull()
        ->and($event->reminder_before_seconds)->toBe(1800)
        ->and($event->remind_at)->not->toBeNull()
        ->and($event->remind_at->diffInSeconds($scheduledAt->copy()->subMinutes(30)))->toBeLessThan(2)
        ->and($event->reminder_dismissed_at)->toBeNull();
});

test('scheduling a site visit with reminder stores remind_at', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'assigned_to_id' => $user->id,
    ]);
    $property = Property::factory()->create();
    $scheduledAt = now()->addHours(3);

    $this->post('/acme/leads/'.$lead->id.'/site-visit', [
        'upcoming_site_visit_at' => $scheduledAt->format('Y-m-d H:i:s'),
        'property_id' => $property->id,
        'visit_type' => 'fresh_visit',
        'add_reminder' => 1,
        'reminder_hours' => 1,
        'reminder_minutes' => 0,
        'reminder_seconds' => 0,
    ])->assertRedirect();

    $event = LeadScheduledEvent::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadScheduledEventType::SiteVisit)
        ->latest('id')
        ->first();

    expect($event)->not->toBeNull()
        ->and($event->reminder_before_seconds)->toBe(3600)
        ->and($event->remind_at->diffInSeconds($scheduledAt->copy()->subHour()))->toBeLessThan(2);
});

test('creating a task with reminder stores remind_at', function () {
    createTestTenant();
    $user = actingAsTenantUser();
    $lead = Lead::factory()->create(['assigned_to_id' => $user->id]);
    $dueAt = now()->addHours(4);

    $this->post('/acme/tasks', [
        'lead_id' => $lead->id,
        'title' => 'Reminder Task',
        'due_at' => $dueAt->format('Y-m-d H:i:s'),
        'add_reminder' => 1,
        'reminder_hours' => 0,
        'reminder_minutes' => 15,
        'reminder_seconds' => 0,
    ])->assertRedirect();

    $task = LeadTask::query()->where('title', 'Reminder Task')->first();

    expect($task)->not->toBeNull()
        ->and($task->reminder_before_seconds)->toBe(900)
        ->and($task->remind_at->diffInSeconds($dueAt->copy()->subMinutes(15)))->toBeLessThan(2);
});

test('due reminders endpoint returns assignee reminders and dismiss hides them', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Due Reminder Lead',
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => now()->addHour(),
    ]);

    $event = scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->addHour(),
        'remind_at' => now()->subMinute(),
        'reminder_before_seconds' => 3600,
    ]);

    $task = LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Due Reminder Task',
        'assigned_to_id' => $user->id,
        'status' => TaskStatus::Pending,
        'due_at' => now()->addHour(),
        'remind_at' => now()->subMinutes(2),
        'reminder_before_seconds' => 900,
    ]);

    $this->getJson('/acme/reminders/due')
        ->assertOk()
        ->assertJsonCount(2, 'reminders')
        ->assertJsonFragment(['subject_type' => 'scheduled_event', 'subject_id' => $event->id])
        ->assertJsonFragment(['subject_type' => 'task', 'subject_id' => $task->id]);

    $this->postJson('/acme/reminders/dismiss', [
        'subject_type' => 'scheduled_event',
        'subject_id' => $event->id,
    ])->assertOk()->assertJson(['dismissed' => true]);

    expect($event->fresh()->reminder_dismissed_at)->not->toBeNull();

    $this->getJson('/acme/reminders/due')
        ->assertOk()
        ->assertJsonCount(1, 'reminders')
        ->assertJsonFragment(['subject_type' => 'task', 'subject_id' => $task->id]);
});

test('follow-up reminder validation rejects zero duration', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();

    $this->from('/acme/leads')
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'priority' => 'normal',
            'add_reminder' => 1,
            'reminder_hours' => 0,
            'reminder_minutes' => 0,
            'reminder_seconds' => 0,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('reminder_hours');
});

test('schedule popups include add reminder controls', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Popup Reminder Lead']);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Add reminder')
        ->assertSee('name="reminder_hours"', false)
        ->assertSee('name="reminder_minutes"', false)
        ->assertSee('name="reminder_seconds"', false);

    $this->get('/acme/tasks')
        ->assertOk()
        ->assertSee('Add reminder')
        ->assertSee('name="reminder_hours"', false);
});
