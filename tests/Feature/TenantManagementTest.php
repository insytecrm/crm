<?php

use App\Enums\TenantStatus;
use App\Models\PartnerSubscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function tenantPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Acme Inc',
        'slug' => 'acme',
        'email' => 'office@acme.test',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
        'admin_password_confirmation' => 'password',
    ], $overrides);
}

test('guests are redirected away from company management', function () {
    $this->get(route('tenants.index'))->assertRedirect(route('login'));
});

test('non super admins cannot view companies', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tenants.index'))
        ->assertForbidden();
});

test('super admins can view companies', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'Globex']);

    $this->actingAs($admin)
        ->get(route('tenants.index'))
        ->assertSee('Globex')
        ->assertSee('Channel Partners')
        ->assertSee('Manage all businesses using InSyte.')
        ->assertSee('Add Channel Partner')
        ->assertSee('title="Overview"', false)
        ->assertSee('title="Edit"', false)
        ->assertSee('uiPopover', false);
});

test('super admins can search channel partners', function () {
    $admin = User::factory()->superAdmin()->create();
    Tenant::factory()->create(['name' => 'Globex Realty', 'email' => 'hello@globex.test']);
    Tenant::factory()->create(['name' => 'Initech Homes', 'email' => 'team@initech.test']);

    $this->actingAs($admin)
        ->get(route('tenants.index', ['search' => 'Globex']))
        ->assertOk()
        ->assertSee('Globex Realty')
        ->assertDontSee('Initech Homes');
});

test('super admins can filter channel partners by status', function () {
    $admin = User::factory()->superAdmin()->create();
    Tenant::factory()->create(['name' => 'Active Partner', 'status' => TenantStatus::Active]);
    Tenant::factory()->suspended()->create(['name' => 'Paused Partner']);

    $this->actingAs($admin)
        ->get(route('tenants.index', ['status' => 'suspended']))
        ->assertOk()
        ->assertSee('Paused Partner')
        ->assertDontSee('Active Partner');
});

test('super admins can create a company with its own database', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('tenants.store'), tenantPayload())
        ->assertRedirect(route('tenants.show', 'acme'));

    $tenant = Tenant::query()->find('acme');

    expect($tenant)->not->toBeNull()
        ->and($tenant->name)->toBe('Acme Inc')
        ->and($tenant->status)->toBe(TenantStatus::Active);

    expect($tenant->database()->manager()->databaseExists($tenant->database()->getName()))->toBeTrue();

    $tenant->run(function () {
        expect(DB::table('users')->where('email', 'admin@acme.test')->exists())->toBeTrue();
    });
});

test('creating a company rejects a reserved slug', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->from(route('tenants.create'))
        ->post(route('tenants.store'), tenantPayload(['slug' => 'platform']))
        ->assertRedirect(route('tenants.create'))
        ->assertSessionHasErrors('slug');
});

test('creating a company rejects a duplicate slug', function () {
    $admin = User::factory()->superAdmin()->create();
    Tenant::factory()->create(['id' => 'acme']);

    $this->actingAs($admin)
        ->from(route('tenants.create'))
        ->post(route('tenants.store'), tenantPayload())
        ->assertRedirect(route('tenants.create'))
        ->assertSessionHasErrors('slug');
});

test('super admins can update a company', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'Old Name']);

    $this->actingAs($admin)
        ->put(route('tenants.update', $tenant), [
            'name' => 'New Name',
            'email' => 'new@example.test',
            'status' => TenantStatus::Suspended->value,
        ])
        ->assertRedirect(route('tenants.show', $tenant));

    $tenant->refresh();

    expect($tenant->name)->toBe('New Name')
        ->and($tenant->email)->toBe('new@example.test')
        ->and($tenant->status)->toBe(TenantStatus::Suspended);
});

test('channel partners list edit opens drawer markup and returns to list after save', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'Globex Realty']);

    $response = $this->actingAs($admin)->get(route('tenants.index'));

    $response->assertOk()
        ->assertSee('open-edit-partner')
        ->assertSee('Edit Channel Partner')
        ->assertSee('data-partner-id="'.$tenant->id.'"', false)
        ->assertDontSee('@js($tenant->id)')
        ->assertDontSee('id))"');

    $this->actingAs($admin)
        ->from(route('tenants.index'))
        ->put(route('tenants.update', $tenant), [
            'name' => 'Globex Updated',
            'email' => 'hello@globex.test',
            'status' => TenantStatus::Active->value,
            '_return_to' => 'index',
            '_editing_tenant' => $tenant->id,
        ])
        ->assertRedirect(route('tenants.index'));

    expect($tenant->fresh()->name)->toBe('Globex Updated');
});

test('super admins can delete a company and its database', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('tenants.store'), tenantPayload(['slug' => 'globex', 'admin_email' => 'admin@globex.test']));

    $tenant = Tenant::query()->find('globex');
    $databaseName = $tenant->database()->getName();

    expect($tenant->database()->manager()->databaseExists($databaseName))->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('tenants.destroy', $tenant))
        ->assertRedirect(route('tenants.index'));

    expect(Tenant::query()->find('globex'))->toBeNull()
        ->and($tenant->database()->manager()->databaseExists($databaseName))->toBeFalse();
});

test('channel partners list edit drawer shows delete when there is no active subscription', function () {
    $admin = User::factory()->superAdmin()->create();
    Tenant::factory()->create(['name' => 'No Sub Realty']);

    $this->actingAs($admin)
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertSee('Delete this channel partner and its database?', false);
});

test('channel partners list edit drawer hides delete when an active subscription exists', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'Subscribed Realty']);
    PartnerSubscription::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertDontSee('Delete this channel partner and its database?', false);
});

test('super admins cannot delete a company with an active subscription', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create();
    PartnerSubscription::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)
        ->from(route('tenants.index'))
        ->delete(route('tenants.destroy', $tenant))
        ->assertRedirect(route('tenants.index'))
        ->assertSessionHasErrors('tenant');

    expect(Tenant::query()->find($tenant->id))->not->toBeNull();
});

test('super admins can delete a company after its subscription is cancelled', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create();
    PartnerSubscription::factory()->cancelled()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)
        ->delete(route('tenants.destroy', $tenant))
        ->assertRedirect(route('tenants.index'))
        ->assertSessionHasNoErrors();

    expect(Tenant::query()->find($tenant->id))->toBeNull();
});
