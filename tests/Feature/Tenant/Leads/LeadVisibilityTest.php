<?php

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;

test('administrator can see and work on unassigned leads', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $unassignedLead = Lead::factory()->create([
        'name' => 'Unassigned Lead',
        'assigned_to_id' => null,
    ]);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Unassigned Lead');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$unassignedLead->id)
        ->assertOk()
        ->assertSee('Unassigned Lead');
});

test('non-admin users only see leads assigned to them', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $assignedLead = Lead::factory()->create([
        'name' => 'Assigned Lead',
        'assigned_to_id' => $agent->id,
    ]);

    Lead::factory()->create([
        'name' => 'Unassigned Lead',
        'assigned_to_id' => null,
    ]);

    Lead::factory()->create([
        'name' => 'Admin Lead',
        'assigned_to_id' => $admin->id,
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Assigned Lead')
        ->assertDontSee('Unassigned Lead')
        ->assertDontSee('Admin Lead');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$assignedLead->id)
        ->assertOk()
        ->assertSee('Assigned Lead');
});

test('non-admin users cannot access unassigned or other users leads', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $unassignedLead = Lead::factory()->create([
        'name' => 'Unassigned Lead',
        'assigned_to_id' => null,
    ]);

    $adminLead = Lead::factory()->create([
        'name' => 'Admin Lead',
        'assigned_to_id' => $admin->id,
    ]);

    actingAsTenantUser($agent);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$unassignedLead->id)
        ->assertNotFound();

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$adminLead->id)
        ->assertNotFound();
});
