<?php

use App\Contracts\AiChatClient;
use App\Enums\AiConversationStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\TaskStatus;
use App\Enums\TenantPermission;
use App\Models\AiConversation;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Ai\FakeAiChatClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('guests are redirected from the ai chat endpoint', function () {
    createTestTenant();

    $this->postJson('/acme/ai/chat', [
        'message' => 'Schedule a follow-up',
    ])->assertUnauthorized();
});

test('empty chat prompts are rejected', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->postJson('/acme/ai/chat', [
        'message' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('message');
});

test('users without ai permission cannot chat', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::factory()->create(['name' => 'No AI']);
    $role->permissions()->sync(
        Permission::query()->where('key', TenantPermission::DashboardView->value)->pluck('id'),
    );

    $viewer = User::query()->create([
        'name' => 'Viewer User',
        'email' => 'viewer-ai-chat@acme.test',
        'password' => 'password',
        'role_id' => $role->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($viewer);

    $this->postJson('/acme/ai/chat', [
        'message' => 'Find Rahul',
    ])->assertForbidden();
});

test('chat replies without changing crm records when no tool is used', function () {
    createTestTenant();
    actingAsTenantUser();

    $client = app(AiChatClient::class);
    expect($client)->toBeInstanceOf(FakeAiChatClient::class);
    $client->queueText('Ask me to schedule a follow-up or find a lead.');

    $this->postJson('/acme/ai/chat', [
        'message' => 'What can you do?',
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Ask me to schedule a follow-up or find a lead.')
        ->assertJsonPath('status', AiConversationStatus::Open->value);

    expect(LeadScheduledEvent::query()->count())->toBe(0)
        ->and(AiConversation::query()->count())->toBe(1);
});

test('chat can schedule a follow-up through existing crm actions', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Rahul Sharma',
    ]);

    $scheduledAt = now()->addDay()->startOfHour();

    $client = app(AiChatClient::class);
    $client->queueTool('schedule_follow_up', [
        'lead_id' => $lead->id,
        'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
        'priority' => 'normal',
        'notes' => 'Confirm inventory',
    ]);
    $client->queueText('Follow-up scheduled for Rahul Sharma.');

    $this->postJson('/acme/ai/chat', [
        'message' => 'Schedule a follow-up with Rahul Sharma tomorrow',
    ])
        ->assertOk()
        ->assertJsonPath('status', AiConversationStatus::Completed->value)
        ->assertJsonPath('reply', 'Follow-up scheduled for Rahul Sharma.');

    $lead->refresh();

    expect(LeadScheduledEvent::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadScheduledEventType::FollowUp)
        ->exists())->toBeTrue()
        ->and($lead->next_follow_up_at?->format('Y-m-d H:i:s'))->toBe($scheduledAt->format('Y-m-d H:i:s'));
});

test('chat can complete a task through existing crm actions', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Task Lead',
    ]);
    $task = LeadTask::factory()->inProgress()->create([
        'lead_id' => $lead->id,
        'title' => 'Send brochure',
        'assigned_to_id' => $user->id,
        'created_by_id' => $user->id,
    ]);

    $client = app(AiChatClient::class);
    $client->queueTool('complete_task', [
        'task_id' => $task->id,
    ]);
    $client->queueText('Task marked complete.');

    $this->postJson('/acme/ai/chat', [
        'message' => 'Mark the send brochure task complete',
    ])->assertOk();

    expect($task->fresh()->status)->toBe(TaskStatus::Complete)
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

test('agents cannot schedule follow-ups on leads they cannot see', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-ai-tools@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $hiddenLead = Lead::factory()->create([
        'name' => 'Hidden Lead',
        'assigned_to_id' => $admin->id,
    ]);

    actingAsTenantUser($agent);

    $client = app(AiChatClient::class);
    $client->queueTool('schedule_follow_up', [
        'lead_id' => $hiddenLead->id,
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
    ]);
    $client->queueText('I could not find that lead.');

    $this->postJson('/acme/ai/chat', [
        'message' => 'Schedule a follow-up with Hidden Lead tomorrow',
    ])->assertOk();

    expect(LeadScheduledEvent::query()->where('lead_id', $hiddenLead->id)->count())->toBe(0);
});

test('chat cannot continue another users conversation', function () {
    createTestTenant();
    actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Other Owner',
        'email' => 'other-ai@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $conversation = AiConversation::factory()->create([
        'user_id' => $manager->id,
        'title' => 'Private conversation',
    ]);

    $this->postJson('/acme/ai/chat', [
        'message' => 'Continue',
        'conversation_id' => $conversation->id,
    ])->assertNotFound();
});
