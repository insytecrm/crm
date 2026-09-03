<?php

use App\Enums\TenantPermission;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

test('administrator can create a custom role with permission switches', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/settings?tab=roles')
        ->post('/acme/settings/roles', [
            'name' => 'Sales Rep',
            'description' => 'Can work leads only',
            'permissions' => [
                TenantPermission::DashboardView->value,
                TenantPermission::LeadsView->value,
                TenantPermission::LeadsCreate->value,
            ],
        ])
        ->assertRedirect('/acme/settings?tab=roles')
        ->assertSessionHas('status');

    $role = Role::query()->where('slug', 'sales-rep')->first();

    expect($role)->not->toBeNull()
        ->and($role->grants(TenantPermission::LeadsView))->toBeTrue()
        ->and($role->grants(TenantPermission::BookingsView))->toBeFalse();
});

test('administrator can update a custom role permissions', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->create([
        'name' => 'Coordinator',
        'slug' => 'coordinator',
        'description' => 'Initial role',
        'is_system' => false,
    ]);

    $this->from('/acme/settings?tab=roles')
        ->patch('/acme/settings/roles/'.$role->id, [
            'name' => 'Coordinator',
            'description' => 'Updated role',
            'permissions' => [
                TenantPermission::TasksView->value,
                TenantPermission::TasksManage->value,
            ],
        ])
        ->assertRedirect('/acme/settings?tab=roles');

    $role->refresh();

    expect($role->description)->toBe('Updated role')
        ->and($role->grants(TenantPermission::TasksManage))->toBeTrue()
        ->and($role->grants(TenantPermission::LeadsView))->toBeFalse();
});

test('administrator cannot delete a system role', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->where('slug', 'manager')->firstOrFail();

    $this->from('/acme/settings?tab=roles')
        ->delete('/acme/settings/roles/'.$role->id)
        ->assertRedirect('/acme/settings?tab=roles')
        ->assertSessionHas('status');

    expect(Role::query()->whereKey($role->id)->exists())->toBeTrue();
});

test('administrator can delete an unused custom role', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->create([
        'name' => 'Temporary Role',
        'slug' => 'temporary-role',
        'is_system' => false,
    ]);

    $this->from('/acme/settings?tab=roles')
        ->delete('/acme/settings/roles/'.$role->id)
        ->assertRedirect('/acme/settings?tab=roles');

    expect(Role::query()->whereKey($role->id)->exists())->toBeFalse();
});

test('administrator role always grants every permission', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    expect($admin->hasPermission(TenantPermission::SettingsRoles))->toBeTrue()
        ->and($admin->hasPermission(TenantPermission::IntegrationsManage))->toBeTrue();
});

test('agent role denies user management permissions', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    expect($agent->hasPermission(TenantPermission::SettingsUsers))->toBeFalse()
        ->and($agent->hasPermission(TenantPermission::LeadsView))->toBeTrue();
});

test('tenant provisioning syncs permissions for future feature keys', function () {
    createTestTenant();

    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    expect(Permission::query()->count())->toBe(count(TenantPermission::cases()))
        ->and(Permission::query()->where('key', TenantPermission::IntegrationsManage->value)->exists())->toBeTrue();
});

test('administrator can deactivate and reactivate a custom role', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->create([
        'name' => 'Toggle Role',
        'slug' => 'toggle-role',
        'is_system' => false,
        'is_active' => true,
    ]);

    $this->from('/acme/settings?tab=roles')
        ->patch('/acme/settings/roles/'.$role->id.'/status', [
            'is_active' => '0',
        ])
        ->assertRedirect('/acme/settings?tab=roles');

    expect($role->fresh()->is_active)->toBeFalse()
        ->and($role->fresh()->grants(TenantPermission::LeadsView))->toBeFalse();

    $this->from('/acme/settings?tab=roles')
        ->patch('/acme/settings/roles/'.$role->id.'/status', [
            'is_active' => '1',
        ])
        ->assertRedirect('/acme/settings?tab=roles');

    expect($role->fresh()->is_active)->toBeTrue();
});

test('administrator role status cannot be changed', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->where('slug', 'administrator')->firstOrFail();

    $this->from('/acme/settings?tab=roles')
        ->patch('/acme/settings/roles/'.$role->id.'/status', [
            'is_active' => '0',
        ])
        ->assertRedirect('/acme/settings?tab=roles')
        ->assertSessionHas('status');

    expect($role->fresh()->is_active)->toBeTrue();
});
