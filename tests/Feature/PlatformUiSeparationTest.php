<?php

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;

test('platform companies page uses platform ui chrome without tenant crm navigation', function () {
    $admin = User::factory()->superAdmin()->create();
    Tenant::factory()->create([
        'name' => 'Globex',
        'status' => TenantStatus::Active,
    ]);

    $this->actingAs($admin)
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertSee('Channel Partners')
        ->assertSee('Globex')
        ->assertSee('Active')
        ->assertSee('Suspended')
        ->assertSee('Manage all businesses using InSyte.')
        ->assertDontSee('InSyte AI OS')
        ->assertDontSee('Priority Leads')
        ->assertSee('uiPopover', false);
});

test('platform company show page renders platform panels', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'Initech']);

    $this->actingAs($admin)
        ->get(route('tenants.show', $tenant))
        ->assertOk()
        ->assertSee('Initech')
        ->assertSee('Access Workspace')
        ->assertSee('Account Snapshot')
        ->assertSee('Overview')
        ->assertDontSee('InSyte AI OS')
        ->assertDontSee('Priority Leads');
});

test('platform profile page uses platform panels without tenant settings chrome', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Profile')
        ->assertSee('Profile Information')
        ->assertSee('Update Password')
        ->assertSee('Delete Account')
        ->assertSee('Settings')
        ->assertSee('Channel Partners')
        ->assertDontSee('InSyte AI OS')
        ->assertDontSee('Priority Leads');
});
