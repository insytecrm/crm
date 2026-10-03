<?php

use App\Enums\AutomationActionType;
use App\Enums\AutomationConditionField;
use App\Enums\AutomationConditionOperator;
use App\Enums\AutomationRunStatus;
use App\Enums\AutomationTrigger;
use App\Enums\LeadLostReason;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Enums\ScheduledActivityPriority;
use App\Models\Automation;
use App\Models\AutomationAction;
use App\Models\AutomationCondition;
use App\Models\AutomationRun;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;

test('creating a lead assigned to the owner runs an active lead created workflow', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::LeadCreated,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Welcome call'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Automated Lead',
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $lead = Lead::query()->where('name', 'Automated Lead')->first();
    $run = AutomationRun::query()->first();

    expect($lead)->not->toBeNull()
        ->and(LeadTask::query()->where('lead_id', $lead->id)->where('title', 'Welcome call')->exists())->toBeTrue()
        ->and($run)->not->toBeNull()
        ->and($run->dry_run)->toBeFalse()
        ->and($run->status)->toBe(AutomationRunStatus::Succeeded)
        ->and($run->lead_id)->toBe($lead->id)
        ->and($workflow->fresh()->last_run_at)->not->toBeNull();
});

test('another users workflow does not run for a lead assigned to the current user', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Other Owner',
        'email' => 'manager-engine@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $workflow = Automation::factory()->active()->create([
        'user_id' => $manager->id,
        'trigger' => AutomationTrigger::LeadCreated,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Manager only task'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Admin Lead',
        'assigned_to_id' => $admin->id,
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    expect(Lead::query()->where('name', 'Admin Lead')->exists())->toBeTrue()
        ->and(AutomationRun::query()->count())->toBe(0)
        ->and(LeadTask::query()->where('title', 'Manager only task')->exists())->toBeFalse();
});

test('a lead assigned to a manager runs that managers workflow', function () {
    createTestTenant();
    actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Assigned Manager',
        'email' => 'assigned-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $workflow = Automation::factory()->active()->create([
        'user_id' => $manager->id,
        'trigger' => AutomationTrigger::LeadCreated,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Manager welcome'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Manager Assigned Lead',
        'assigned_to_id' => $manager->id,
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $lead = Lead::query()->where('name', 'Manager Assigned Lead')->first();

    expect($lead)->not->toBeNull()
        ->and($lead->assigned_to_id)->toBe($manager->id)
        ->and(LeadTask::query()->where('lead_id', $lead->id)->where('title', 'Manager welcome')->exists())->toBeTrue()
        ->and(AutomationRun::query()->where('automation_id', $workflow->id)->where('status', AutomationRunStatus::Succeeded)->exists())->toBeTrue();
});

test('changing lead status runs a status changed workflow', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'assigned_to_id' => $user->id,
        'status' => LeadStatus::New,
    ]);

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::StatusChanged,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::AddNote,
        'config' => ['body' => 'Status changed by workflow'],
    ]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->patch('/acme/leads/'.$lead->id.'/status', [
            'status' => LeadStatus::Contacted->value,
        ]);

    expect(LeadNote::query()->where('lead_id', $lead->id)->where('body', 'Status changed by workflow')->exists())->toBeTrue()
        ->and(AutomationRun::query()->where('automation_id', $workflow->id)->where('status', AutomationRunStatus::Succeeded)->exists())->toBeTrue();
});

test('scheduling a follow-up on an existing lead runs a follow-up scheduled workflow', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => null,
    ]);

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::FollowUpScheduled,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Prepare for follow-up'],
    ]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'priority' => ScheduledActivityPriority::Normal->value,
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    expect(LeadTask::query()->where('lead_id', $lead->id)->where('title', 'Prepare for follow-up')->exists())->toBeTrue()
        ->and(AutomationRun::query()->where('automation_id', $workflow->id)->where('status', AutomationRunStatus::Succeeded)->exists())->toBeTrue();
});

test('an automation status change does not recurse into another workflow', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $created = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::LeadCreated,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $created->id,
        'type' => AutomationActionType::ChangeStatus,
        'config' => ['status' => LeadStatus::Contacted->value],
    ]);

    $statusChanged = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::StatusChanged,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $statusChanged->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Recursion task'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Recursion Lead',
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $lead = Lead::query()->where('name', 'Recursion Lead')->first();

    expect($lead)->not->toBeNull()
        ->and($lead->status)->toBe(LeadStatus::Contacted)
        ->and(LeadTask::query()->where('title', 'Recursion task')->exists())->toBeFalse();
});

test('unmatched conditions skip the workflow without writing actions', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::LeadCreated,
    ]);
    AutomationCondition::factory()->create([
        'automation_id' => $workflow->id,
        'field' => AutomationConditionField::Status,
        'operator' => AutomationConditionOperator::Equals,
        'value' => ['text' => LeadStatus::Qualified->value],
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Qualified only'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Unmatched Lead',
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $run = AutomationRun::query()->where('automation_id', $workflow->id)->first();

    expect(LeadTask::query()->where('title', 'Qualified only')->exists())->toBeFalse()
        ->and($run)->not->toBeNull()
        ->and($run->status)->toBe(AutomationRunStatus::Skipped)
        ->and($workflow->fresh()->last_run_at)->toBeNull();
});

test('a closed lead is skipped', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::FollowUpScheduled,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Closed lead task'],
    ]);

    $lead = Lead::factory()->converted()->create([
        'assigned_to_id' => $user->id,
    ]);

    $this->from('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->post('/acme/automations/workflows/'.$workflow->id.'/test', [
            'lead_id' => $lead->id,
        ])
        ->assertRedirect('/acme/automations/workflows/'.$workflow->id.'/edit')
        ->assertSessionHas('status', 'This workflow skipped a closed lead.');

    expect(LeadTask::query()->where('title', 'Closed lead task')->exists())->toBeFalse()
        ->and(AutomationRun::query()->where('status', AutomationRunStatus::Skipped)->exists())->toBeTrue();
});

test('marking a lead lost runs status changed workflows for closed leads', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'assigned_to_id' => $user->id,
        'status' => LeadStatus::Qualified,
    ]);

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::StatusChanged,
    ]);
    AutomationCondition::factory()->create([
        'automation_id' => $workflow->id,
        'field' => AutomationConditionField::Status,
        'operator' => AutomationConditionOperator::Equals,
        'value' => ['text' => LeadStatus::Lost->value],
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::AddNote,
        'config' => ['body' => 'Lost lead follow-up note'],
    ]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/mark-lost', [
            'lost_reasons' => [LeadLostReason::BudgetMismatch->value],
            'closing_notes' => 'Too expensive',
        ])
        ->assertRedirect();

    expect($lead->fresh()->status)->toBe(LeadStatus::Lost)
        ->and(LeadNote::query()->where('lead_id', $lead->id)->where('body', 'Lost lead follow-up note')->exists())->toBeTrue()
        ->and(AutomationRun::query()->where('automation_id', $workflow->id)->where('status', AutomationRunStatus::Succeeded)->exists())->toBeTrue();
});

test('an unassigned lead does not run live workflows', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::StatusChanged,
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Unassigned task'],
    ]);

    $lead = Lead::factory()->create([
        'assigned_to_id' => null,
        'status' => LeadStatus::New,
    ]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->patch('/acme/leads/'.$lead->id.'/status', [
            'status' => LeadStatus::Contacted->value,
        ]);

    expect(LeadTask::query()->where('title', 'Unassigned task')->exists())->toBeFalse()
        ->and(AutomationRun::query()->count())->toBe(0);
});

test('scheduling a follow-up still creates the follow-up when no workflow matches', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => null,
    ]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'priority' => ScheduledActivityPriority::Normal->value,
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    expect($lead->fresh()->next_follow_up_at)->not->toBeNull()
        ->and(LeadScheduledEvent::query()->where('lead_id', $lead->id)->count())->toBe(1)
        ->and(AutomationRun::query()->count())->toBe(0);
});

test('a property project filter matches leads with the same type and location', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    Property::factory()->create([
        'project_name' => 'Skyline Residences',
        'project_location' => 'Baner',
        'property_type' => PropertyType::Apartment,
    ]);

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::LeadCreated,
    ]);
    AutomationCondition::factory()->create([
        'automation_id' => $workflow->id,
        'field' => AutomationConditionField::PropertyType,
        'operator' => AutomationConditionOperator::Equals,
        'value' => ['text' => 'Skyline Residences'],
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Skyline task'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Skyline Lead',
        'location' => 'Baner',
        'property_type' => PropertyType::Apartment->value,
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    expect(LeadTask::query()->where('title', 'Skyline task')->exists())->toBeTrue();
});

test('a property project filter skips leads in a different location', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    Property::factory()->create([
        'project_name' => 'Skyline Residences',
        'project_location' => 'Baner',
        'property_type' => PropertyType::Apartment,
    ]);

    $workflow = Automation::factory()->active()->create([
        'user_id' => $user->id,
        'trigger' => AutomationTrigger::LeadCreated,
    ]);
    AutomationCondition::factory()->create([
        'automation_id' => $workflow->id,
        'field' => AutomationConditionField::PropertyType,
        'operator' => AutomationConditionOperator::Equals,
        'value' => ['text' => 'Skyline Residences'],
    ]);
    AutomationAction::factory()->create([
        'automation_id' => $workflow->id,
        'type' => AutomationActionType::CreateTask,
        'config' => ['title' => 'Skyline task'],
    ]);

    $this->post('/acme/leads', [
        'name' => 'Other Location Lead',
        'location' => 'Koregaon Park',
        'property_type' => PropertyType::Apartment->value,
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $run = AutomationRun::query()->where('automation_id', $workflow->id)->first();

    expect(LeadTask::query()->where('title', 'Skyline task')->exists())->toBeFalse()
        ->and($run)->not->toBeNull()
        ->and($run->status)->toBe(AutomationRunStatus::Skipped);
});
