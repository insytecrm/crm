<?php

use App\Enums\TenantPermission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('administrator can view users and roles tabs in settings', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings')
        ->assertOk()
        ->assertSee('Users')
        ->assertSee('Roles & Permissions');
});

test('agent cannot view users and roles tabs in settings', function () {
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

    $this->get('/acme/settings')
        ->assertOk()
        ->assertSee('Profile')
        ->assertDontSee('Roles & Permissions');
});

test('administrator can create a user from settings', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->where('slug', 'agent')->firstOrFail();

    $this->from('/acme/settings?tab=users')
        ->post('/acme/settings/users', [
            'name' => 'New Team Member',
            'email' => 'member@acme.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
        ])
        ->assertRedirect('/acme/settings?tab=users')
        ->assertSessionHas('status');

    expect(User::query()->where('email', 'member@acme.test')->exists())->toBeTrue();
});

test('administrator can update and delete a user from settings', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->where('slug', 'manager')->firstOrFail();
    $user = User::query()->create([
        'name' => 'Temp User',
        'email' => 'temp@acme.test',
        'password' => 'password',
        'role_id' => $role->id,
        'email_verified_at' => now(),
    ]);

    $this->from('/acme/settings?tab=users')
        ->patch('/acme/settings/users/'.$user->id, [
            'name' => 'Updated Temp User',
            'email' => 'temp@acme.test',
            'role_id' => $role->id,
        ])
        ->assertRedirect('/acme/settings?tab=users');

    expect($user->fresh()->name)->toBe('Updated Temp User');

    $this->from('/acme/settings?tab=users')
        ->delete('/acme/settings/users/'.$user->id)
        ->assertRedirect('/acme/settings?tab=users');

    expect(User::query()->whereKey($user->id)->exists())->toBeFalse();
});

test('administrator cannot delete their own account', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $this->from('/acme/settings?tab=users')
        ->delete('/acme/settings/users/'.$admin->id)
        ->assertRedirect('/acme/settings?tab=users')
        ->assertSessionHas('status');

    expect(User::query()->whereKey($admin->id)->exists())->toBeTrue();
});

test('agent cannot manage users', function () {
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

    $this->post('/acme/settings/users', [
        'name' => 'Blocked User',
        'email' => 'blocked@acme.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $agentRole->id,
    ])->assertForbidden();
});

test('administrator can deactivate and reactivate a user', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::query()->where('slug', 'agent')->firstOrFail();
    $user = User::query()->create([
        'name' => 'Toggle User',
        'email' => 'toggle@acme.test',
        'password' => 'password',
        'role_id' => $role->id,
        'email_verified_at' => now(),
        'is_active' => true,
    ]);

    $this->from('/acme/settings?tab=users')
        ->patch('/acme/settings/users/'.$user->id.'/status', [
            'is_active' => '0',
        ])
        ->assertRedirect('/acme/settings?tab=users');

    expect($user->fresh()->is_active)->toBeFalse()
        ->and($user->fresh()->hasPermission(TenantPermission::LeadsView))->toBeFalse();
});

test('administrator cannot deactivate their own account', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $this->from('/acme/settings?tab=users')
        ->patch('/acme/settings/users/'.$admin->id.'/status', [
            'is_active' => '0',
        ])
        ->assertRedirect('/acme/settings?tab=users')
        ->assertSessionHas('status');

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('deactivated users cannot log in', function () {
    createTestTenant();
    actingAsTenantUser();

    Auth::logout();

    $role = Role::query()->where('slug', 'agent')->firstOrFail();
    User::query()->create([
        'name' => 'Inactive User',
        'email' => 'inactive@acme.test',
        'password' => 'password',
        'role_id' => $role->id,
        'email_verified_at' => now(),
        'is_active' => false,
    ]);

    $this->from('/acme/login')
        ->post('/acme/login', [
            'email' => 'inactive@acme.test',
            'password' => 'password',
        ])
        ->assertSessionHasErrors('email');
});
