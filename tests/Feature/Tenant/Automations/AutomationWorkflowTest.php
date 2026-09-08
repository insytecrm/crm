<?php

use App\Actions\RunAutomation;
use App\Enums\AutomationActionType;
use App\Enums\AutomationConditionField;
use App\Enums\AutomationConditionOperator;
use App\Enums\AutomationRunStatus;
use App\Enums\AutomationTrigger;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Automation;
use App\Models\AutomationAction;
use App\Models\AutomationRun;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;

test('guests are redirected from workflows page', function () {
    createTestTenant();

    $this->get('/acme/automations/workflows')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('tenant users can view their workflows list', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    Automation::factory()->create([
        'user_id' => $user->id,
        'name' => 'Welcome task',
        'trigger' => AutomationTrigger::LeadCreated,
    ]);

    $this->get('/acme/automations/workflows')
        ->assertOk()
        ->assertSee('Workflows')
        ->assertSee('Create workflow')
        ->assertSee('Welcome task')
        ->assertSee('Lead created')
        ->assertDontSee('Coming soon');
});

test('workflows list does not show another users workflows', function () {
    createTestTenant();
    actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Other Owner',
        'email' => 'other-owner@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    Automation::factory()->create([
        'user_id' => $manager->id,
        'name' => 'Private manager workflow',
    ]);

    $this->get('/acme/automations/workflows')
        ->assertOk()
        ->assertDontSee('Private manager workflow');
});

test('agent cannot view workflows', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-automations@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/automations/workflows')
        ->assertForbidden();
});

test('tenant users can create an untitled workflow', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/automations/workflows')
        ->assertRedirect();

    $workflow = Automation::query()->first();

    expect($workflow)->not->toBeNull()
        ->and($workflow->name)->toBe('Untitled workflow')
        ->and($workflow->isActive())->toBeFalse()
        ->and($workflow->trigger)->toBeNull();

    $this->get('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertOk()
        ->assertSee('Untitled workflow')
        ->assertSee('Trigger')
        ->assertSee('Action')
        ->assertSee('Select a trigger')
        ->assertSee('Select an action')
        ->assertSee('Choose the event that starts this workflow.')
        ->assertSee('workflow-picker-host-root', false)
        ->assertSee('workflow-picker-drawer', false)
        ->assertSee('Lead created')
        ->assertSee('Create task')
        ->assertSee('Add note')
        ->assertSee('Change lead status')
        ->assertSee('Schedule follow-up')
        ->assertSee('Wait / delay')
        ->assertSee('Send WhatsApp')
        ->assertSee('Messages')
        ->assertSee('Coming soon')
        ->assertDontSee('Test with a lead');
});

test('workflow filter values use current status budget property type source and location lists', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    Lead::factory()->create([
        'assigned_to_id' => $user->id,
        'source' => LeadSource::Referral->value,
        'location' => 'Koregaon Park',
    ]);
    Property::factory()->create([
        'project_name' => 'Skyline Residences',
        'developer_name' => 'Acme Builders',
        'project_location' => 'Baner',
    ]);

    $workflow = Automation::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->get('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertOk()
        ->assertSee('Apartment')
        ->assertSee('below_50_lakh', false)
        ->assertSee('Skyline Residences')
        ->assertSee('Referral')
        ->assertSee('Koregaon Park')
        ->assertSee('Baner');
});

test('tenant users can save a workflow with a trigger filter and action', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->create([
        'user_id' => $user->id,
        'name' => 'Untitled workflow',
    ]);

    $this->from('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->patch('/acme/automations/workflows/'.$workflow->id, [
            'name' => 'New lead task',
            'is_active' => '1',
            'trigger' => AutomationTrigger::LeadCreated->value,
            'conditions' => [
                [
                    'field' => AutomationConditionField::Status->value,
                    'operator' => AutomationConditionOperator::Equals->value,
                    'value' => LeadStatus::New->value,
                ],
            ],
            'actions' => [
                [
                    'type' => AutomationActionType::CreateTask->value,
                    'title' => 'Call the lead',
                ],
            ],
        ])
        ->assertRedirect('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertSessionHas('status');

    $workflow->refresh()->load(['conditions', 'actions']);

    expect($workflow->name)->toBe('New lead task')
        ->and($workflow->isActive())->toBeTrue()
        ->and($workflow->trigger)->toBe(AutomationTrigger::LeadCreated)
        ->and($workflow->conditions)->toHaveCount(1)
        ->and($workflow->conditions->first()->field)->toBe(AutomationConditionField::Status)
        ->and($workflow->conditions->first()->valueText())->toBe(LeadStatus::New->value)
        ->and($workflow->actions)->toHaveCount(1)
        ->and($workflow->actions->first()->type)->toBe(AutomationActionType::CreateTask)
        ->and($workflow->actions->first()->config['title'] ?? null)->toBe('Call the lead');
});

test('saving a workflow requires a name', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->from('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->patch('/acme/automations/workflows/'.$workflow->id, [
            'name' => '',
            'trigger' => AutomationTrigger::LeadCreated->value,
        ])
        ->assertRedirect('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertSessionHasErrors(['name' => 'Please name this workflow.']);
});

test('a workflow cannot be turned on without a trigger', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->create([
        'user_id' => $user->id,
        'trigger' => null,
        'is_active' => false,
    ]);

    $this->from('/acme/automations/workflows')
        ->patch('/acme/automations/workflows/'.$workflow->id.'/status', [
            'is_active' => '1',
        ])
        ->assertRedirect('/acme/automations/workflows')
        ->assertSessionHasErrors(['is_active' => 'Choose a trigger before turning this workflow on.']);

    expect($workflow->fresh()->isActive())->toBeFalse();
});

test('tenant users can turn a workflow off', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'name' => 'Active workflow',
    ]);

    $this->from('/acme/automations/workflows')
        ->patch('/acme/automations/workflows/'.$workflow->id.'/status', [
            'is_active' => '0',
        ])
        ->assertRedirect('/acme/automations/workflows')
        ->assertSessionHas('status');

    expect($workflow->fresh()->isActive())->toBeFalse();
});

test('tenant users can delete their workflow', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->create([
        'user_id' => $user->id,
        'name' => 'Disposable workflow',
    ]);

    $this->from('/acme/automations/workflows')
        ->delete('/acme/automations/workflows/'.$workflow->id)
        ->assertRedirect('/acme/automations/workflows')
        ->assertSessionHas('status');

    expect(Automation::query()->whereKey($workflow->id)->exists())->toBeFalse();
});

test('testing a workflow records a dry run without changing leads', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'name' => 'Testable workflow',
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Dry run task'],
    ]);

    $lead = Lead::factory()->create([
        'name' => 'Existing Lead',
        'assigned_to_id' => $user->id,
    ]);

    $this->from('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->post('/acme/automations/workflows/'.$workflow->id.'/test', [
            'lead_id' => $lead->id,
        ])
        ->assertRedirect('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertSessionHas('status', 'Would create task: Dry run task');

    $run = AutomationRun::query()->first();

    expect($run)->not->toBeNull()
        ->and($run->dry_run)->toBeTrue()
        ->and($run->status)->toBe(AutomationRunStatus::Tested)
        ->and($run->automation_id)->toBe($workflow->id)
        ->and($workflow->fresh()->last_run_at)->toBeNull()
        ->and(Lead::query()->where('name', 'Existing Lead')->exists())->toBeTrue()
        ->and(LeadTask::query()->where('title', 'Dry run task')->exists())->toBeFalse();
});

test('testing a workflow requires a lead', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
    ]);

    $this->from('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->post('/acme/automations/workflows/'.$workflow->id.'/test')
        ->assertRedirect('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertSessionHasErrors(['lead_id' => 'Choose a lead to test this workflow.']);
});

test('another user cannot open someone elses workflow', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $workflow = Automation::factory()->create([
        'user_id' => $admin->id,
        'name' => 'Admin only workflow',
    ]);

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Manager User',
        'email' => 'manager-automations@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($manager);

    $this->get('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertNotFound();
});

test('workflow names are escaped on the list', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    Automation::factory()->create([
        'user_id' => $user->id,
        'name' => "O'Reilly <script>alert('xss')</script>",
    ]);

    $this->get('/acme/automations/workflows')
        ->assertOk()
        ->assertDontSee("<script>alert('xss')</script>", false)
        ->assertSee('Reilly');
});

test('inactive workflows do not run when a lead is created', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->create([
        'user_id' => $user->id,
        'name' => 'Should not fire',
        'trigger' => AutomationTrigger::LeadCreated,
        'is_active' => false,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Should not exist'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Automation Safety Lead',
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    expect(Lead::query()->where('name', 'Automation Safety Lead')->exists())->toBeTrue()
        ->and(AutomationRun::query()->count())->toBe(0)
        ->and(LeadTask::query()->where('title', 'Should not exist')->exists())->toBeFalse();
});

test('a live run without a lead is skipped', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
    ]);

    $run = app(RunAutomation::class)->handle($workflow, [], false);

    expect($run->dry_run)->toBeFalse()
        ->and($run->status)->toBe(AutomationRunStatus::Skipped)
        ->and($workflow->fresh()->last_run_at)->toBeNull();
});
