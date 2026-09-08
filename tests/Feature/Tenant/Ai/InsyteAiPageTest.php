<?php

use App\Enums\TenantPermission;
use App\Models\AiConversation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('guests are redirected from the ai os page', function () {
    createTestTenant();

    $this->get('/acme/ai')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('tenant users can view the ai os page', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/ai')
        ->assertOk()
        ->assertSee('InSyte AI OS')
        ->assertSee('Beta')
        ->assertSee('Chat')
        ->assertSee('Lead Insights')
        ->assertSee('Try these examples')
        ->assertSee('Schedule a site visit')
        ->assertSee('Recent AI conversations')
        ->assertSee("Today's suggestions")
        ->assertSee('Quick actions')
        ->assertSee('Add Lead')
        ->assertSee(route('tenant.leads.index', ['tenant' => 'acme', 'add' => 1], false));
});

test('tenant sidebar includes insyte ai os', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('InSyte AI OS')
        ->assertSee(route('tenant.ai.index', ['tenant' => 'acme'], false));
});

test('users without ai permission cannot view the ai os page', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::factory()->create(['name' => 'Viewer']);
    $role->permissions()->sync(
        Permission::query()->where('key', TenantPermission::DashboardView->value)->pluck('id'),
    );

    $viewer = User::query()->create([
        'name' => 'Viewer User',
        'email' => 'viewer-ai@acme.test',
        'password' => 'password',
        'role_id' => $role->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($viewer);

    $this->get('/acme/ai')->assertForbidden();
    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertDontSee(route('tenant.ai.index', ['tenant' => 'acme'], false));
});

test('agent can view the ai os page', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-ai@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/ai')
        ->assertOk()
        ->assertSee('InSyte AI OS');
});

test('conversation titles are escaped on the ai os page', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    AiConversation::factory()->create([
        'user_id' => $user->id,
        'title' => '<script>alert("xss")</script>',
    ]);

    $this->get('/acme/ai')
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
});
