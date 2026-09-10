<?php

use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\User;

test('due follow-up for assignee appears in activity notifications feed and popups', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Due Follow Lead',
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => now()->subMinute(),
    ]);

    $event = scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->subMinute(),
        'user_id' => $user->id,
    ]);

    $this->getJson('/acme/activity-notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('popups.0.subject_id', $event->id)
        ->assertJsonPath('popups.0.kind', 'follow_up')
        ->assertJsonPath('popups.0.lead.name', 'Due Follow Lead')
        ->assertJsonPath('notifications.0.title', 'Follow-up due');
});

test('dismissing due popup keeps unread notification in the feed', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Dismiss Popup Lead',
        'assigned_to_id' => $user->id,
    ]);

    $event = scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->subMinutes(5),
        'user_id' => $user->id,
    ]);

    $this->getJson('/acme/activity-notifications')->assertOk();

    $this->postJson('/acme/activity-notifications/dismiss-popup', [
        'subject_type' => 'scheduled_event',
        'subject_id' => $event->id,
    ])->assertOk()->assertJson(['dismissed' => true]);

    expect($event->fresh()->reminder_dismissed_at)->not->toBeNull()
        ->and($user->fresh()->unreadNotifications)->toHaveCount(1);

    $this->getJson('/acme/activity-notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('popups', [])
        ->assertJsonPath('notifications.0.body', 'Dismiss Popup Lead');
});

test('due task for assignee is included and mark all read clears unread count', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Task Due Lead',
        'assigned_to_id' => $user->id,
    ]);

    $task = LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Call bank',
        'assigned_to_id' => $user->id,
        'created_by_id' => $user->id,
        'status' => TaskStatus::Pending,
        'due_at' => now()->subMinute(),
    ]);

    $this->getJson('/acme/activity-notifications')
        ->assertOk()
        ->assertJsonPath('popups.0.kind', 'task')
        ->assertJsonPath('popups.0.subject_id', $task->id)
        ->assertJsonPath('unread_count', 1);

    $this->postJson('/acme/activity-notifications/read-all')
        ->assertOk()
        ->assertJson(['read' => true]);

    $this->getJson('/acme/activity-notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 0);
});

test('users do not receive due notifications for leads assigned to someone else', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $assignee = User::query()->create([
        'name' => 'Other Rep',
        'email' => 'other-rep@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
        'is_active' => true,
        'role_id' => $admin->role_id,
    ]);

    $lead = Lead::factory()->create([
        'name' => 'Other Assignee Lead',
        'assigned_to_id' => $assignee->id,
    ]);

    scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->subMinute(),
        'user_id' => $assignee->id,
    ]);

    $this->getJson('/acme/activity-notifications')
        ->assertOk()
        ->assertJsonPath('popups', [])
        ->assertJsonPath('unread_count', 0);
});

test('tenant header includes notifications control', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Notifications', false)
        ->assertSee('Mark all read', false)
        ->assertSee('activityDueNotifications', false);
});
