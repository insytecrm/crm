<?php

use App\Actions\IssueTenantLeadApiToken;
use App\Enums\TenantPermission;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

test('integrations tab shows an api card that opens the lead api page', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings?tab=integrations')
        ->assertOk()
        ->assertSee('API')
        ->assertSee(route('tenant.settings.integrations.api', ['tenant' => 'acme'], false));

    $this->get('/acme/settings/integrations/api')
        ->assertOk()
        ->assertSee('Lead API')
        ->assertSee(route('api.v1.leads.store'))
        ->assertSee('crm_');
});

test('lead api settings page issues a token when missing', function () {
    createTestTenant();
    actingAsTenantUser();

    expect(Tenant::query()->find('acme')->lead_api_token_hash)->toBeNull();

    $this->get('/acme/settings/integrations/api')->assertOk();

    expect(Tenant::query()->find('acme')->lead_api_token_hash)->not->toBeNull();
});

test('authorized users can regenerate the lead api key', function () {
    createTestTenant();
    actingAsTenantUser();

    $tenant = Tenant::query()->findOrFail('acme');
    $original = app(IssueTenantLeadApiToken::class)->handle($tenant);
    $originalHash = $tenant->fresh()->lead_api_token_hash;

    $this->from('/acme/settings/integrations/api')
        ->post('/acme/settings/integrations/api/regenerate')
        ->assertRedirect('/acme/settings/integrations/api')
        ->assertSessionHas('status');

    $tenant->refresh();

    expect($tenant->lead_api_token_hash)->not->toBe($originalHash)
        ->and($tenant->lead_api_token_encrypted)->not->toBe($original);
});

test('users without integrations manage cannot regenerate the lead api key', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $role = Role::query()->create([
        'name' => 'Viewer',
        'slug' => 'viewer',
        'description' => 'View integrations only',
        'is_system' => false,
    ]);

    $viewPermission = Permission::query()->where('key', TenantPermission::IntegrationsView->value)->firstOrFail();
    $role->permissions()->sync([$viewPermission->id]);

    $user = User::query()->create([
        'name' => 'Integration Viewer',
        'email' => 'viewer@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
        'role_id' => $role->id,
    ]);

    actingAsTenantUser($user);

    $this->post('/acme/settings/integrations/api/regenerate')
        ->assertForbidden();
});
