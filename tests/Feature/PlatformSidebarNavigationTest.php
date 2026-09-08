<?php

use App\Models\User;

test('super admins see the platform sidebar stub menus', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Channel Partners')
        ->assertSee('Plans')
        ->assertSee('Revenue & Billing')
        ->assertSee('Quotations')
        ->assertSee('Integrations')
        ->assertSee('Analytics')
        ->assertSee('Utilities')
        ->assertSee('Settings')
        ->assertDontSee('InSyte AI OS')
        ->assertDontSee('Priority Leads');
});

test('super admins can open each platform sidebar stub page', function (string $routeName, string $heading) {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route($routeName))
        ->assertOk()
        ->assertSee($heading)
        ->assertSee('Coming soon');
})->with([
    ['platform.integrations', 'Integrations'],
    ['platform.analytics', 'Analytics'],
    ['platform.utilities', 'Utilities'],
    ['platform.settings', 'Settings'],
]);

test('super admins can open revenue and billing instead of a stub', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.revenue'))
        ->assertOk()
        ->assertSee('Revenue & Billing')
        ->assertDontSee('Coming soon');
});

test('super admins can open quotations instead of a stub', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.quotations'))
        ->assertOk()
        ->assertSee('Quotations')
        ->assertSee('Manage commercial proposals sent to Channel Partners.')
        ->assertDontSee('Coming soon');
});

test('guests are redirected away from platform stub pages', function () {
    $this->get(route('platform.plans'))->assertRedirect(route('login'));
});

test('non super admins cannot view platform stub pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('platform.plans'))
        ->assertForbidden();
});
