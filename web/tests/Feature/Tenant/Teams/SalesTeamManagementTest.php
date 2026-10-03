<?php

use App\Models\Role;
use App\Models\SalesTeam;
use App\Models\User;

test('administrator can view teams index', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/teams')
        ->assertOk()
        ->assertSee('Teams')
        ->assertSee('Add Team');
});

test('agent cannot view teams index', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-teams@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/teams')
        ->assertForbidden();
});

test('administrator can create a team with a manager', function () {
    createTestTenant();
    actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Sales Manager',
        'email' => 'manager-teams@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $this->from('/acme/teams')
        ->post('/acme/teams', [
            'name' => 'North Sales',
            'description' => 'Northern region team',
            'manager_id' => $manager->id,
            'is_active' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $team = SalesTeam::query()->where('name', 'North Sales')->first();

    expect($team)->not->toBeNull()
        ->and($team->manager_id)->toBe($manager->id)
        ->and($team->isActive())->toBeTrue();
});

test('team creation rejects non-manager users as manager', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Not A Manager',
        'email' => 'not-manager@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $this->from('/acme/teams')
        ->post('/acme/teams', [
            'name' => 'Invalid Team',
            'manager_id' => $agent->id,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('manager_id');

    expect(SalesTeam::query()->where('name', 'Invalid Team')->exists())->toBeFalse();
});

test('administrator can view update and archive a team', function () {
    createTestTenant();
    actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Team Manager',
        'email' => 'team-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $newManager = User::query()->create([
        'name' => 'Replacement Manager',
        'email' => 'replacement-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::factory()->create([
        'name' => 'West Sales',
        'manager_id' => $manager->id,
        'created_by_id' => tenantUser()->id,
    ]);

    $this->get('/acme/teams/'.$team->id)
        ->assertOk()
        ->assertSee('West Sales')
        ->assertSee('Team Manager');

    $this->from('/acme/teams/'.$team->id)
        ->patch('/acme/teams/'.$team->id, [
            'name' => 'West Sales Updated',
            'description' => 'Updated description',
            'manager_id' => $newManager->id,
            'is_active' => '0',
        ])
        ->assertRedirect('/acme/teams/'.$team->id)
        ->assertSessionHas('status');

    $team->refresh();

    expect($team->name)->toBe('West Sales Updated')
        ->and($team->manager_id)->toBe($newManager->id)
        ->and($team->isActive())->toBeFalse();

    $this->from('/acme/teams')
        ->patch('/acme/teams/'.$team->id.'/status', [
            'is_active' => '1',
        ])
        ->assertRedirect('/acme/teams')
        ->assertSessionHas('status');

    expect($team->fresh()->isActive())->toBeTrue();

    $this->from('/acme/teams')
        ->delete('/acme/teams/'.$team->id)
        ->assertRedirect('/acme/teams')
        ->assertSessionHas('status');

    expect(SalesTeam::query()->whereKey($team->id)->exists())->toBeFalse()
        ->and(SalesTeam::withTrashed()->whereKey($team->id)->exists())->toBeTrue();
});

test('administrator can add and remove team members', function () {
    createTestTenant();
    actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();

    $manager = User::query()->create([
        'name' => 'Member Team Manager',
        'email' => 'member-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    $agent = User::query()->create([
        'name' => 'Team Agent',
        'email' => 'team-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::factory()->create([
        'name' => 'Member Team',
        'manager_id' => $manager->id,
        'created_by_id' => tenantUser()->id,
    ]);

    $this->from('/acme/teams/'.$team->id)
        ->post('/acme/teams/'.$team->id.'/members', [
            'user_id' => $agent->id,
        ])
        ->assertRedirect('/acme/teams/'.$team->id)
        ->assertSessionHas('status');

    expect($team->fresh()->members()->whereKey($agent->id)->exists())->toBeTrue();

    $this->from('/acme/teams/'.$team->id)
        ->delete('/acme/teams/'.$team->id.'/members/'.$agent->id)
        ->assertRedirect('/acme/teams/'.$team->id)
        ->assertSessionHas('status');

    expect($team->fresh()->members()->whereKey($agent->id)->exists())->toBeFalse();
});

test('manager role can view teams but cannot manage them', function () {
    createTestTenant();
    actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'View Only Manager',
        'email' => 'view-manager@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($manager);

    $this->get('/acme/teams')
        ->assertOk()
        ->assertDontSee('Add Team');

    $team = SalesTeam::factory()->create([
        'name' => 'Read Only Team',
        'manager_id' => $manager->id,
    ]);

    $this->post('/acme/teams', [
        'name' => 'Blocked Team',
        'manager_id' => $manager->id,
    ])->assertForbidden();
});
