<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('tenant sidebar shows settings link at the bottom', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee(route('tenant.settings.index', ['tenant' => 'acme'], false));
});

test('tenant users can view settings with tabs', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings')
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('Profile')
        ->assertSee('Company')
        ->assertSee('Security')
        ->assertSee('Notifications')
        ->assertSee('Users')
        ->assertSee('Roles & Permissions')
        ->assertSee('Acme Inc');
});

test('tenant users can open a specific settings tab from the query string', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings?tab=security')
        ->assertOk()
        ->assertSee('Update Password');
});

test('tenant users can update their profile from settings', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $this->from('/acme/settings')
        ->patch('/acme/settings/profile', [
            'name' => 'Updated Admin',
            'email' => 'updated-admin@acme.test',
        ])
        ->assertRedirect('/acme/settings?tab=profile')
        ->assertSessionHas('status');

    $user->refresh();

    expect($user->name)->toBe('Updated Admin')
        ->and($user->email)->toBe('updated-admin@acme.test');
});

test('tenant users can update company details from settings', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/settings?tab=company')
        ->patch('/acme/settings/company', [
            'name' => 'Acme Realty',
            'email' => 'hello@acme.test',
        ])
        ->assertRedirect('/acme/settings?tab=company')
        ->assertSessionHas('status');

    $company = Tenant::query()->find('acme');

    expect($company->name)->toBe('Acme Realty')
        ->and($company->email)->toBe('hello@acme.test');
});

test('tenant users can update their password from settings', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $this->from('/acme/settings?tab=security')
        ->put('/acme/settings/password', [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
        ->assertRedirect('/acme/settings?tab=security')
        ->assertSessionHas('status');

    $user->refresh();

    expect(Hash::check('new-secure-password', $user->password))->toBeTrue();
});

test('settings profile update validates unique email within tenant', function () {
    createTestTenant();
    actingAsTenantUser();

    User::query()->create([
        'name' => 'Other User',
        'email' => 'taken@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->from('/acme/settings')
        ->patch('/acme/settings/profile', [
            'name' => 'Admin',
            'email' => 'taken@acme.test',
        ])
        ->assertSessionHasErrors('email');
});
