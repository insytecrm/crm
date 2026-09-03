<?php

use App\Enums\TenantStatus;
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
        ->assertSee('Companies')
        ->assertSee('Platform')
        ->assertSee('uiPopover', false);
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
