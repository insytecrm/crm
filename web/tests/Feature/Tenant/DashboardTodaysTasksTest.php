<?php

use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadTask;

test('dashboard shows todays incomplete tasks', function () {
    createTestTenant();
    actingAsTenantUser();

    $todayLead = Lead::factory()->create(['name' => 'Today Task Lead']);
    LeadTask::factory()->create([
        'lead_id' => $todayLead->id,
        'title' => 'Call Today Task',
        'due_at' => now()->addHours(2),
        'status' => TaskStatus::Pending,
    ]);

    $tomorrowLead = Lead::factory()->create(['name' => 'Tomorrow Task Lead']);
    LeadTask::factory()->create([
        'lead_id' => $tomorrowLead->id,
        'title' => 'Tomorrow Only Task',
        'due_at' => now()->addDay(),
        'status' => TaskStatus::Pending,
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Today\'s Tasks')
        ->assertSee('Call Today Task')
        ->assertSee('Today Task Lead')
        ->assertDontSee('Tomorrow Only Task')
        ->assertViewHas('todaysTasks', function ($tasks) use ($todayLead): bool {
            return $tasks->count() === 1
                && $tasks->first()->title === 'Call Today Task'
                && $tasks->first()->lead->is($todayLead);
        });
});

test('dashboard todays tasks includes overdue tasks and start actions', function () {
    createTestTenant();
    actingAsTenantUser();

    $overdueLead = Lead::factory()->create(['name' => 'Overdue Task Lead']);
    $task = LeadTask::factory()->create([
        'lead_id' => $overdueLead->id,
        'title' => 'Overdue Dashboard Task',
        'due_at' => now()->subDay(),
        'status' => TaskStatus::Pending,
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Overdue Dashboard Task')
        ->assertSee('Overdue')
        ->assertSee(route('tenant.tasks.status.update', $task, false), false)
        ->assertDontSee(route('tenant.tasks.complete', $task, false), false);
});

test('dashboard todays tasks excludes completed tasks', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Done Task Lead']);
    LeadTask::factory()->complete()->create([
        'lead_id' => $lead->id,
        'title' => 'Already Done Task',
        'due_at' => now()->subHour(),
        'completed_at' => now(),
    ]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('No tasks for today.')
        ->assertDontSee('Already Done Task');
});
