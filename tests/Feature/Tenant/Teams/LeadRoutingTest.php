<?php

use App\Actions\CreateLead;
use App\Enums\LeadRoutingDistribution;
use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\LeadRoutingRule;
use App\Models\Role;
use App\Models\SalesTeam;
use App\Models\User;

test('administrator can create a lead routing rule from the teams page', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Routing Manager',
        'email' => 'routing-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agentA = User::query()->create([
        'name' => 'Agent A',
        'email' => 'agent-a-routing@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);
    $agentB = User::query()->create([
        'name' => 'Agent B',
        'email' => 'agent-b-routing@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::query()->create([
        'name' => 'North Routing',
        'manager_id' => $manager->id,
        'is_active' => true,
        'created_by_id' => $admin->id,
    ]);
    $team->members()->attach([$agentA->id, $agentB->id]);

    $this->from('/acme/teams')
        ->post('/acme/teams/routing-rules', [
            'source' => LeadSource::Facebook->value,
            'sub_source' => '',
            'sales_team_id' => $team->id,
            'distribution' => LeadRoutingDistribution::Custom->value,
            'is_active' => '1',
            'members' => [
                ['user_id' => $agentA->id, 'weight' => 2],
                ['user_id' => $agentB->id, 'weight' => 1],
            ],
        ])
        ->assertRedirect(route('tenant.teams.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $rule = LeadRoutingRule::query()->first();

    expect($rule)->not->toBeNull()
        ->and($rule->source)->toBe(LeadSource::Facebook->value)
        ->and($rule->sub_source)->toBeNull()
        ->and($rule->distribution->value)->toBe('custom')
        ->and($rule->members)->toHaveCount(2)
        ->and((int) $rule->members->firstWhere('id', $agentA->id)->pivot->weight)->toBe(2);
});

test('routing assigns unassigned facebook leads by custom weights', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Weight Manager',
        'email' => 'weight-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agentA = User::query()->create([
        'name' => 'Weight A',
        'email' => 'weight-a@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);
    $agentB = User::query()->create([
        'name' => 'Weight B',
        'email' => 'weight-b@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::query()->create([
        'name' => 'Weight Team',
        'manager_id' => $manager->id,
        'is_active' => true,
        'created_by_id' => $admin->id,
    ]);
    $team->members()->attach([$agentA->id, $agentB->id]);

    $rule = LeadRoutingRule::query()->create([
        'source' => LeadSource::Facebook->value,
        'sub_source' => null,
        'sales_team_id' => $team->id,
        'distribution' => LeadRoutingDistribution::Custom,
        'is_active' => true,
        'distribution_cursor' => 0,
        'created_by_id' => $admin->id,
    ]);
    $rule->members()->sync([
        $agentA->id => ['weight' => 2],
        $agentB->id => ['weight' => 1],
    ]);

    $createLead = app(CreateLead::class);

    $first = $createLead->handle([
        'name' => 'Routed One',
        'source' => LeadSource::Facebook->value,
    ], $admin);
    $second = $createLead->handle([
        'name' => 'Routed Two',
        'source' => LeadSource::Facebook->value,
    ], $admin);
    $third = $createLead->handle([
        'name' => 'Routed Three',
        'source' => LeadSource::Facebook->value,
    ], $admin);

    expect($first->assigned_to_id)->toBe($agentA->id)
        ->and($second->assigned_to_id)->toBe($agentA->id)
        ->and($third->assigned_to_id)->toBe($agentB->id);
});

test('explicit assignee still wins over routing', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Explicit Manager',
        'email' => 'explicit-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Routed Agent',
        'email' => 'routed-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::query()->create([
        'name' => 'Explicit Team',
        'manager_id' => $manager->id,
        'is_active' => true,
        'created_by_id' => $admin->id,
    ]);
    $team->members()->attach([$agent->id]);

    $rule = LeadRoutingRule::query()->create([
        'source' => LeadSource::Facebook->value,
        'sales_team_id' => $team->id,
        'distribution' => LeadRoutingDistribution::RoundRobin,
        'is_active' => true,
        'created_by_id' => $admin->id,
    ]);
    $rule->members()->sync([$agent->id => ['weight' => 1]]);

    $lead = app(CreateLead::class)->handle([
        'name' => 'Manual Assign',
        'source' => LeadSource::Facebook->value,
        'assigned_to_id' => $admin->id,
    ], $admin);

    expect($lead->assigned_to_id)->toBe($admin->id)
        ->and(Lead::query()->where('name', 'Manual Assign')->value('assigned_to_id'))->toBe($admin->id);
});

test('routing matches facebook campaign name inside lead sub source path', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Campaign Manager',
        'email' => 'campaign-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Campaign Agent',
        'email' => 'campaign-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::query()->create([
        'name' => 'Campaign Team',
        'manager_id' => $manager->id,
        'is_active' => true,
        'created_by_id' => $admin->id,
    ]);
    $team->members()->attach([$agent->id]);

    $rule = LeadRoutingRule::query()->create([
        'source' => LeadSource::Facebook->value,
        'sub_source' => 'Summer Campaign',
        'sales_team_id' => $team->id,
        'distribution' => LeadRoutingDistribution::RoundRobin,
        'is_active' => true,
        'created_by_id' => $admin->id,
    ]);
    $rule->members()->sync([$agent->id => ['weight' => 1]]);

    $lead = app(CreateLead::class)->handle([
        'name' => 'FB Path Lead',
        'source' => LeadSource::Facebook->value,
        'sub_source' => 'North Page › Summer Campaign › Lead Form',
    ], $admin);

    expect($lead->assigned_to_id)->toBe($agent->id);
});
