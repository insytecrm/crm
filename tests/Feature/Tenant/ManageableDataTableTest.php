<?php

use App\Models\Lead;
use App\Models\LeadTask;
use App\Support\DataTable\DataTableRegistry;
use App\Support\DataTable\Definitions\FollowUpsTableDefinition;
use App\Support\DataTable\Definitions\TasksTableDefinition;
use App\Support\DataTable\TablePreferencesSupport;

test('tenant users can persist task table column preferences', function () {
    createTestTenant();
    $user = actingAsTenantUser();
    $definition = new TasksTableDefinition;

    $this->patchJson('/acme/table-preferences/tasks', [
        'listing' => 'all',
        'columns' => array_merge($definition->defaultColumns(), [
            'description' => true,
            'assigned_to' => false,
        ]),
        'custom_columns' => [
            [
                'label' => 'Priority tag',
                'type' => 'select',
                'options' => ['Hot', 'Cold'],
                'visible' => true,
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('columns.assigned_to', false)
        ->assertJsonPath('columns.description', true)
        ->assertJsonCount(1, 'custom_columns');

    $user->refresh();
    $preferences = $user->dataTablePreferences('tasks', $definition, 'all');

    expect($preferences['columns']['assigned_to'])->toBeFalse()
        ->and($preferences['custom_columns'][0]['label'])->toBe('Priority tag')
        ->and($preferences['custom_columns'][0]['options'])->toBe(['Hot', 'Cold']);
});

test('task table column preferences are stored separately per listing', function () {
    createTestTenant();
    $user = actingAsTenantUser();
    $definition = new TasksTableDefinition;

    $this->patchJson('/acme/table-preferences/tasks', [
        'listing' => 'all',
        'columns' => array_merge($definition->defaultColumns(), [
            'assigned_to' => false,
        ]),
        'custom_columns' => [],
    ])->assertOk();

    $this->patchJson('/acme/table-preferences/tasks', [
        'listing' => 'in_progress',
        'columns' => array_merge($definition->defaultColumns(), [
            'assigned_to' => true,
            'description' => true,
        ]),
        'custom_columns' => [],
    ])->assertOk();

    $user->refresh();

    expect($user->dataTablePreferences('tasks', $definition, 'all')['columns']['assigned_to'])->toBeFalse()
        ->and($user->dataTablePreferences('tasks', $definition, 'in_progress')['columns']['assigned_to'])->toBeTrue()
        ->and($user->dataTablePreferences('tasks', $definition, 'in_progress')['columns']['description'])->toBeTrue();
});

test('tasks index shows manageable table controls', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    LeadTask::factory()->for($lead)->create([
        'title' => 'Checkbox Task',
        'due_at' => now(),
    ]);

    $this->get('/acme/tasks?filter=all')
        ->assertOk()
        ->assertSee('Edit Columns', false)
        ->assertSee(__('Select all'), false)
        ->assertSee('Checkbox Task', false);
});

test('follow-up table column preferences are stored separately per stage', function () {
    createTestTenant();
    $user = actingAsTenantUser();
    $definition = new FollowUpsTableDefinition;

    $this->patchJson('/acme/table-preferences/follow_ups', [
        'listing' => 'pending',
        'columns' => array_merge($definition->defaultColumns(), [
            'phone' => false,
        ]),
        'custom_columns' => [],
    ])->assertOk();

    $this->patchJson('/acme/table-preferences/follow_ups', [
        'listing' => 'overdue',
        'columns' => array_merge($definition->defaultColumns(), [
            'phone' => true,
            'priority' => false,
        ]),
        'custom_columns' => [],
    ])->assertOk();

    $user->refresh();

    expect($user->dataTablePreferences('follow_ups', $definition, 'pending')['columns']['phone'])->toBeFalse()
        ->and($user->dataTablePreferences('follow_ups', $definition, 'overdue')['columns']['phone'])->toBeTrue()
        ->and($user->dataTablePreferences('follow_ups', $definition, 'overdue')['columns']['priority'])->toBeFalse();
});

test('bookings table preferences remain flat without listing key', function () {
    createTestTenant();
    $user = actingAsTenantUser();
    $definition = DataTableRegistry::get('bookings');

    $this->patchJson('/acme/table-preferences/bookings', [
        'columns' => array_merge($definition->defaultColumns(), [
            'lead' => false,
        ]),
        'custom_columns' => [],
    ])->assertOk();

    expect($user->fresh()->dataTablePreferences('bookings', $definition)['columns']['lead'])->toBeFalse();
});

test('tenant users can bulk delete tasks', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $first = LeadTask::factory()->for($lead)->create();
    $second = LeadTask::factory()->for($lead)->create();

    $this->from('/acme/tasks')
        ->delete('/acme/table-bulk-delete/tasks', [
            'task_ids' => [$first->id, $second->id],
        ])
        ->assertRedirect('/acme/tasks');

    expect(LeadTask::query()->count())->toBe(0);
});

test('tenant users can reset task table preferences', function () {
    createTestTenant();
    $user = actingAsTenantUser();
    $definition = new TasksTableDefinition;
    $preferences = $user->preferences ?? [];
    $preferences[TablePreferencesSupport::storageKey('tasks')] = TablePreferencesSupport::normalize($definition, [
        'columns' => array_merge($definition->defaultColumns(), ['assigned_to' => false]),
    ]);
    $user->preferences = $preferences;
    $user->save();

    $this->patchJson('/acme/table-preferences/tasks', [
        'listing' => 'all',
        'columns' => $definition->defaultColumns(),
        'custom_columns' => [],
    ])->assertOk();

    expect($user->fresh()->dataTablePreferences('tasks', $definition, 'all'))->toBe(TablePreferencesSupport::defaults($definition));
});
