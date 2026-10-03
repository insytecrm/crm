<?php

use App\Models\Lead;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

test('work hub page loads for tenant users', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/work')
        ->assertOk()
        ->assertSee('Work');
});

test('agent users are redirected to work after login', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    expect($agentRole->slug)->toBe('agent');

    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
        'is_active' => true,
    ]);

    $agent->load('role');
    expect($agent->isAgentRole())->toBeTrue();

    $this->post('/acme/login', [
        'email' => $agent->email,
        'password' => 'password',
    ])->assertRedirect(route('tenant.work.index', absolute: false));
});

test('follow ups index redirects to activities with kind filter', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/follow-ups')
        ->assertRedirect('/acme/activities?kind=follow_up');
});

test('lead search returns matching leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Searchable Person', 'phone' => '9998887776']);

    $this->getJson('/acme/leads/search?q=Searchable')
        ->assertOk()
        ->assertJsonPath('results.0.id', $lead->id)
        ->assertJsonPath('results.0.name', 'Searchable Person');
});
