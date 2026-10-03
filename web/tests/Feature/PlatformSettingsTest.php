<?php

use App\Models\User;

test('super admins can view platform settings', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.settings'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('Profile')
        ->assertSee('Super Admin Users')
        ->assertSee('Roles')
        ->assertSee('Permissions')
        ->assertDontSee('Coming soon');
});

test('super admins can update profile and add another super admin', function () {
    $admin = User::factory()->superAdmin()->create([
        'name' => 'Platform Admin',
        'email' => 'admin@platform.test',
    ]);

    $this->actingAs($admin)
        ->patch(route('platform.settings.profile.update'), [
            'name' => 'Updated Admin',
            'email' => 'admin@platform.test',
        ])
        ->assertRedirect(route('platform.settings', ['tab' => 'profile']))
        ->assertSessionHas('status');

    expect($admin->fresh()->name)->toBe('Updated Admin');

    $this->actingAs($admin)
        ->post(route('platform.settings.users.store'), [
            'name' => 'Second Admin',
            'email' => 'second@platform.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertRedirect(route('platform.settings', ['tab' => 'users']))
        ->assertSessionHas('status');

    expect(User::query()->where('email', 'second@platform.test')->value('is_super_admin'))->toBeTrue();
});

test('non super admins cannot access platform settings', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('platform.settings'))
        ->assertForbidden();
});
