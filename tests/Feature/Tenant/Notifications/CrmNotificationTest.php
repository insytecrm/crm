<?php

use App\Enums\LeadActivityType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\CrmEventNotification;
use Illuminate\Support\Facades\Notification;

test('creating a lead for another user notifies the assignee', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $assignee = User::query()->create([
        'name' => 'Sales Rep',
        'email' => 'sales@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
        'is_active' => true,
        'role_id' => $admin->role_id,
    ]);

    Notification::fake();

    $this->post('/acme/leads', [
        'name' => 'Notified Lead',
        'phone' => '9999999999',
        'assigned_to_id' => $assignee->id,
    ])->assertRedirect();

    $lead = Lead::query()->where('name', 'Notified Lead')->first();

    expect($lead)->not->toBeNull();

    Notification::assertSentTo(
        $assignee,
        CrmEventNotification::class,
        function (CrmEventNotification $notification) use ($lead): bool {
            return $notification->eventType === LeadActivityType::LeadCreated->value
                && $notification->leadId === $lead->id;
        },
    );
});

test('assignee does not receive a notification for their own lead activity', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Own Lead',
        'assigned_to_id' => $user->id,
    ]);

    Notification::fake();

    $this->post('/acme/leads/'.$lead->id.'/follow-up', [
        'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'priority' => 'normal',
        'notes' => 'Call tomorrow',
    ])->assertRedirect();

    Notification::assertNothingSent();
});

test('notifications feed returns events and mark all read clears them', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $assignee = User::query()->create([
        'name' => 'Feed Rep',
        'email' => 'feed@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
        'is_active' => true,
        'role_id' => $admin->role_id,
    ]);

    $lead = Lead::factory()->create([
        'name' => 'Feed Lead',
        'assigned_to_id' => $assignee->id,
    ]);

    $assignee->notify(new CrmEventNotification(
        eventType: LeadActivityType::LeadAssigned->value,
        title: 'Lead Assigned',
        body: 'Feed Lead',
        url: route('tenant.leads.index', ['tenant' => 'acme', 'lead' => $lead->id]),
        leadId: $lead->id,
    ));

    $task = LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Due Feed Task',
        'assigned_to_id' => $assignee->id,
        'created_by_id' => $admin->id,
        'status' => TaskStatus::Pending,
        'due_at' => now()->addHour(),
        'remind_at' => now()->subMinute(),
        'reminder_before_seconds' => 900,
    ]);

    tenancy()->initialize(Tenant::query()->findOrFail('acme'));
    $this->actingAs($assignee);

    $this->getJson('/acme/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 2)
        ->assertJsonFragment(['type' => LeadActivityType::LeadAssigned->value])
        ->assertJsonFragment(['type' => 'task', 'subject_id' => $task->id]);

    $this->postJson('/acme/notifications/read-all')
        ->assertOk()
        ->assertJson(['read' => true]);

    expect($assignee->fresh()->unreadNotifications)->toHaveCount(0)
        ->and($task->fresh()->reminder_dismissed_at)->not->toBeNull();

    $this->getJson('/acme/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 0);
});

test('assigning a lead notifies the new assignee', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $assignee = User::query()->create([
        'name' => 'Assign Rep',
        'email' => 'assign@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
        'is_active' => true,
        'role_id' => $admin->role_id,
    ]);

    $lead = Lead::factory()->create([
        'name' => 'Reassign Lead',
        'assigned_to_id' => $admin->id,
    ]);

    Notification::fake();

    $this->from('/acme/leads')
        ->patch('/acme/leads/bulk/assign', [
            'lead_ids' => [$lead->id],
            'assigned_to_id' => $assignee->id,
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $assignee,
        CrmEventNotification::class,
        fn (CrmEventNotification $notification): bool => $notification->eventType === LeadActivityType::LeadAssigned->value,
    );
});

test('tenant layout renders mark all read control', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Notifications', false)
        ->assertSee('Mark all read', false);
});
