<?php

use App\Actions\EnsurePortalWebhookEndpoint;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PropertyPortal;
use App\Enums\TenantPermission;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\PortalWebhookEndpoint;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

test('integrations tab lists property portal cards', function () {
    createTestTenant();
    actingAsTenantUser();

    $response = $this->get('/acme/settings?tab=integrations')->assertOk();

    foreach (PropertyPortal::cases() as $portal) {
        $response->assertSee($portal->label())
            ->assertSee(route('tenant.settings.integrations.portal', [
                'tenant' => 'acme',
                'portal' => $portal->value,
            ], false));
    }

    $response->assertSee('API')
        ->assertSee('Coming soon');
});

test('portal configure page issues webhook credentials and shows stats', function (PropertyPortal $portal) {
    createTestTenant();
    actingAsTenantUser();

    expect(
        PortalWebhookEndpoint::query()
            ->where('tenant_id', 'acme')
            ->where('portal', $portal->value)
            ->exists()
    )->toBeFalse();

    $this->get('/acme/settings/integrations/portals/'.$portal->value)
        ->assertOk()
        ->assertSee($portal->label())
        ->assertSee('Webhook Configuration')
        ->assertSee('Webhook URL')
        ->assertSee('Webhook Secret')
        ->assertSee('/api/webhooks/'.$portal->value.'/')
        ->assertSee('Total Leads')
        ->assertSee('Recent Leads')
        ->assertSee('whsec_');

    expect(
        PortalWebhookEndpoint::query()
            ->where('tenant_id', 'acme')
            ->where('portal', $portal->value)
            ->exists()
    )->toBeTrue();
})->with(PropertyPortal::cases());

test('portal configure page shows recent leads for that portal source', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create([
        'name' => 'Housing Recent Lead',
        'source' => PropertyPortal::Housing->leadSource()->value,
        'status' => LeadStatus::New,
    ]);

    Lead::factory()->create([
        'name' => 'Other Source Lead',
        'source' => LeadSource::Api->value,
        'status' => LeadStatus::New,
    ]);

    $this->get('/acme/settings/integrations/portals/housing')
        ->assertOk()
        ->assertSee('Housing Recent Lead')
        ->assertDontSee('Other Source Lead');
});

test('authorized users can regenerate a portal webhook secret', function () {
    createTestTenant();
    actingAsTenantUser();

    $endpoint = app(EnsurePortalWebhookEndpoint::class)->handle(
        Tenant::query()->findOrFail('acme'),
        PropertyPortal::MagicBricks,
    );
    $originalHash = $endpoint->secret_hash;

    $this->from('/acme/settings/integrations/portals/magicbricks')
        ->post('/acme/settings/integrations/portals/magicbricks/regenerate')
        ->assertRedirect('/acme/settings/integrations/portals/magicbricks')
        ->assertSessionHas('status');

    expect($endpoint->fresh()->secret_hash)->not->toBe($originalHash);
});

test('users without integrations manage cannot regenerate portal webhook secrets', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $role = Role::query()->create([
        'name' => 'Viewer',
        'slug' => 'portal-viewer',
        'description' => 'View integrations only',
        'is_system' => false,
    ]);

    $viewPermission = Permission::query()->where('key', TenantPermission::IntegrationsView->value)->firstOrFail();
    $role->permissions()->sync([$viewPermission->id]);

    $user = User::query()->create([
        'name' => 'Portal Viewer',
        'email' => 'portal-viewer@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
        'role_id' => $role->id,
    ]);

    actingAsTenantUser($user);

    $this->post('/acme/settings/integrations/portals/nobroker/regenerate')
        ->assertForbidden();
});
