<?php

use App\Enums\LeadActivityType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadTask;

test('tenant users can view tasks home from sidebar', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee(route('tenant.tasks.index', ['tenant' => 'acme'], false));

    $this->get('/acme/tasks')
        ->assertOk()
        ->assertSee('Tasks')
        ->assertSee('Create Task')
        ->assertSee('Today')
        ->assertSee('Upcoming')
        ->assertSee('Completed')
        ->assertSee('All')
        ->assertSee('Related To')
        ->assertSee('Assigned To')
        ->assertSee('Due Date');
});

test('tasks home shows status timeline labels for listed tasks', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Timeline Lead']);

    LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Timeline Task',
        'due_at' => now()->addHours(2),
    ]);

    $this->get('/acme/tasks?filter=today')
        ->assertOk()
        ->assertSee('Timeline')
        ->assertSee('Pending')
        ->assertSee('In Progress')
        ->assertSee('Complete')
        ->assertSee('Cancelled');
});

test('tasks home shows today tasks and completed today tasks', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Task Lead']);

    LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Due Today Task',
        'due_at' => now()->addHours(3),
    ]);

    LeadTask::factory()->complete()->create([
        'lead_id' => $lead->id,
        'title' => 'Completed Today Task',
        'due_at' => now()->subDay(),
        'completed_at' => now(),
    ]);

    LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Future Task',
        'due_at' => now()->addWeek(),
    ]);

    $this->get('/acme/tasks?filter=today')
        ->assertOk()
        ->assertSee('Due Today Task')
        ->assertSee('Completed Today Task')
        ->assertDontSee('Future Task');
});

test('tenant users can create a task from tasks home', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'New Task Lead']);

    $this->post('/acme/tasks', [
        'lead_id' => $lead->id,
        'title' => 'Call back client',
        'description' => 'Discuss pricing',
        'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
        'assigned_to_id' => $user->id,
    ])
        ->assertRedirect('/acme/tasks?filter=today');

    $task = LeadTask::query()->where('lead_id', $lead->id)->first();

    expect($task)->not->toBeNull()
        ->and($task->title)->toBe('Call back client')
        ->and($task->description)->toBe('Discuss pricing')
        ->and($task->assigned_to_id)->toBe($user->id)
        ->and($task->status)->toBe(TaskStatus::Pending);
});

test('tenant users can mark a task complete from tasks home', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Complete Task Lead']);
    $task = LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Finish proposal',
        'due_at' => now()->addHours(2),
    ]);

    $this->from('/acme/tasks?filter=today')
        ->post('/acme/tasks/'.$task->id.'/complete', [
            'filter' => 'today',
        ])
        ->assertRedirect('/acme/tasks?filter=today');

    $task->refresh();

    expect($task->status)->toBe(TaskStatus::Complete)
        ->and($task->completed_at)->not->toBeNull();
});

test('tasks home shows mark complete popup with notes field', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Popup Task Lead']);
    LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Popup Task',
        'due_at' => now()->addHours(2),
    ]);

    $this->get('/acme/tasks?filter=today')
        ->assertOk()
        ->assertSee('What happened?', false)
        ->assertSee('Add completion notes (optional)', false)
        ->assertSee('Mark Complete', false);
});

test('marking a task complete stores optional completion notes', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Notes Task Lead']);
    $task = LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Notes Task',
        'due_at' => now()->addHours(2),
    ]);

    $this->from('/acme/tasks?filter=today')
        ->post('/acme/tasks/'.$task->id.'/complete', [
            'filter' => 'today',
            'notes' => 'Client confirmed budget',
        ])
        ->assertRedirect('/acme/tasks?filter=today');

    $task->refresh();

    $activity = LeadActivity::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadActivityType::TaskCompleted)
        ->first();

    expect($task->completion_notes)->toBe('Client confirmed budget')
        ->and($activity)->not->toBeNull()
        ->and($activity->description)->toContain('Client confirmed budget')
        ->and($activity->metadata['completion_notes'] ?? null)->toBe('Client confirmed budget');
});

test('tenant users can start and cancel a task from tasks home', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Status Task Lead']);
    $task = LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Follow up call',
        'due_at' => now()->addHours(2),
    ]);

    $this->from('/acme/tasks?filter=today')
        ->patch('/acme/tasks/'.$task->id.'/status', [
            'status' => TaskStatus::InProgress->value,
            'filter' => 'today',
        ])
        ->assertRedirect('/acme/tasks?filter=today');

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);

    $this->from('/acme/tasks?filter=today')
        ->patch('/acme/tasks/'.$task->id.'/status', [
            'status' => TaskStatus::Cancelled->value,
            'filter' => 'today',
        ])
        ->assertRedirect('/acme/tasks?filter=today');

    expect($task->fresh()->status)->toBe(TaskStatus::Cancelled);
});

test('completed filter shows only completed tasks', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Filter Lead']);

    LeadTask::factory()->complete()->create([
        'lead_id' => $lead->id,
        'title' => 'Done Task',
        'completed_at' => now()->subDay(),
    ]);

    LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'title' => 'Open Task',
        'due_at' => now()->addDay(),
    ]);

    LeadTask::factory()->cancelled()->create([
        'lead_id' => $lead->id,
        'title' => 'Cancelled Task',
    ]);

    $this->get('/acme/tasks?filter=completed')
        ->assertOk()
        ->assertSee('Done Task')
        ->assertDontSee('Open Task')
        ->assertDontSee('Cancelled Task');
});
