<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('partner user actions are enabled on the users tab', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'abcrealty',
        'name' => 'ABC Realty',
        'admin_name' => 'Rahul Sharma',
        'admin_email' => 'rahul@abcrealty.test',
    ]);

    $this->actingAs($admin)
        ->get(route('tenants.users', $tenant))
        ->assertOk()
        ->assertSee('Rahul Sharma')
        ->assertSee('view-partner-user-', false)
        ->assertSee('edit-partner-user-', false)
        ->assertSee('Reset access and generate a temporary password?', false)
        ->assertSee('Disable this user?', false);
});

test('super admins can update a partner user', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'abcrealty',
        'admin_email' => 'rahul@abcrealty.test',
    ]);

    $userId = null;
    $managerRoleId = null;

    $tenant->run(function () use (&$userId, &$managerRoleId): void {
        $userId = User::query()->where('email', 'rahul@abcrealty.test')->value('id');
        $managerRoleId = Role::query()->where('slug', 'manager')->value('id');
    });

    $this->actingAs($admin)
        ->put(route('tenants.users.update', [$tenant, $userId]), [
            'name' => 'Rahul Updated',
            'email' => 'rahul.updated@abcrealty.test',
            'role_id' => $managerRoleId,
        ])
        ->assertRedirect(route('tenants.users', $tenant))
        ->assertSessionHas('status');

    $tenant->run(function () use ($userId): void {
        $user = User::query()->findOrFail($userId);

        expect($user->name)->toBe('Rahul Updated')
            ->and($user->email)->toBe('rahul.updated@abcrealty.test')
            ->and($user->role?->slug)->toBe('manager');
    });
});

test('super admins can disable and enable a partner user', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'abcrealty',
        'admin_email' => 'owner@abcrealty.test',
    ]);

    $agentId = null;

    $tenant->run(function () use (&$agentId): void {
        $agentRoleId = Role::query()->where('slug', 'agent')->value('id');

        $agent = User::query()->create([
            'name' => 'Agent One',
            'email' => 'agent@abcrealty.test',
            'password' => 'password',
            'role_id' => $agentRoleId,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $agentId = $agent->id;
    });

    $this->actingAs($admin)
        ->patch(route('tenants.users.status', [$tenant, $agentId]), [
            'is_active' => '0',
        ])
        ->assertRedirect(route('tenants.users', $tenant))
        ->assertSessionHas('status', __('User disabled.'));

    $tenant->run(function () use ($agentId): void {
        expect(User::query()->findOrFail($agentId)->is_active)->toBeFalse();
    });

    $this->actingAs($admin)
        ->patch(route('tenants.users.status', [$tenant, $agentId]), [
            'is_active' => '1',
        ])
        ->assertRedirect(route('tenants.users', $tenant))
        ->assertSessionHas('status', __('User enabled.'));

    $tenant->run(function () use ($agentId): void {
        expect(User::query()->findOrFail($agentId)->is_active)->toBeTrue();
    });
});

test('super admins can reset partner user access', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'abcrealty',
        'admin_email' => 'rahul@abcrealty.test',
        'admin_password' => 'password',
    ]);

    $userId = null;

    $tenant->run(function () use (&$userId): void {
        $userId = User::query()->where('email', 'rahul@abcrealty.test')->value('id');
    });

    $this->actingAs($admin)
        ->post(route('tenants.users.reset-access', [$tenant, $userId]))
        ->assertRedirect(route('tenants.users', $tenant))
        ->assertSessionHas('status');

    $status = (string) session('status');

    expect($status)->toStartWith('Access reset. Temporary password: ');

    $temporaryPassword = Str::after($status, 'Access reset. Temporary password: ');

    $tenant->run(function () use ($userId, $temporaryPassword): void {
        $user = User::query()->findOrFail($userId);

        expect(Hash::check($temporaryPassword, $user->password))->toBeTrue()
            ->and($user->is_active)->toBeTrue()
            ->and($user->remember_token)->toBeNull();
    });
});

test('non super admins cannot manage partner users', function () {
    $user = User::factory()->create();
    $tenant = createTestTenant(['slug' => 'abcrealty']);

    $this->actingAs($user)
        ->put(route('tenants.users.update', [$tenant, 1]), [
            'name' => 'Nope',
            'email' => 'nope@example.test',
            'role_id' => 1,
        ])
        ->assertForbidden();
});
