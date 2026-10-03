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
        ->assertSee('My Account')
        ->assertSee('Workspace')
        ->assertSee('Team')
        ->assertSee('Profile')
        ->assertSee('Company')
        ->assertSee('Security')
        ->assertDontSee('Notifications')
        ->assertSee('Users')
        ->assertSee('Roles & Permissions')
        ->assertSee('Domains')
        ->assertSee('Acme Inc');
});

test('tenant users can open a specific settings tab from the query string', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings?tab=security')
        ->assertOk()
        ->assertSee('My Account')
        ->assertSee('Update Password');
});

test('settings deep links open the matching settings group', function (string $tab, string $groupLabel, string $sectionText) {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings?tab='.$tab)
        ->assertOk()
        ->assertSee($groupLabel)
        ->assertSee($sectionText);
})->with([
    'profile' => ['profile', 'My Account', 'Save Profile'],
    'security' => ['security', 'My Account', 'Update Password'],
    'company' => ['company', 'Workspace', 'Save Company'],
    'domains' => ['domains', 'Workspace', 'Manage custom domains for CRM and website'],
    'users' => ['users', 'Team', 'Invite and manage workspace users'],
    'roles' => ['roles', 'Team', 'Create roles and configure permission switches'],
    'integrations' => ['integrations', 'Integrations', 'Connect Your Tools'],
]);

test('tenant users can update their profile from settings', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $this->from('/acme/settings')
        ->patch('/acme/settings/profile', [
            'name' => 'Updated Admin',
            'email' => 'updated-admin@acme.test',
            'phone' => '+91 98765 43210',
        ])
        ->assertRedirect('/acme/settings?tab=profile')
        ->assertSessionHas('status');

    $user->refresh();

    expect($user->name)->toBe('Updated Admin')
        ->and($user->email)->toBe('updated-admin@acme.test')
        ->and($user->phone)->toBe('+91 98765 43210');
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
