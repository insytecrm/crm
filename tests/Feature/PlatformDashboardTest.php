<?php

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;

test('super admins can view the platform dashboard with real partner data', function () {
    $admin = User::factory()->superAdmin()->create(['name' => 'Gaurav']);
    Tenant::factory()->create(['name' => 'ABC Realty', 'status' => TenantStatus::Active]);
    Tenant::factory()->suspended()->create(['name' => 'Paused Homes']);

    $this->actingAs($admin)
        ->get(route('platform.dashboard'))
        ->assertOk()
        ->assertSee('Gaurav')
        ->assertSee('Here\'s what\'s happening across InSyte today.')
        ->assertSee('Channel Partners')
        ->assertSee('Monthly Revenue')
        ->assertSee('Active Trials')
        ->assertSee('Active Users')
        ->assertSee('Needs Attention')
        ->assertSee('Suspended Partners')
        ->assertSee('Revenue')
        ->assertSee('MRR')
        ->assertSee('Channel Partner Overview')
        ->assertSee('Platform Usage')
        ->assertSee('Recent Activity')
        ->assertSee('ABC Realty joined InSyte')
        ->assertSee('Upcoming')
        ->assertDontSee('₹4.82L')
        ->assertDontSee('Failed Payments')
        ->assertDontSee('InSyte AI OS')
        ->assertDontSee('Priority Leads')
        ->assertDontSee('Coming soon');
});

test('platform dashboard shows all clear when nothing needs attention', function () {
    $admin = User::factory()->superAdmin()->create();
    Tenant::factory()->create(['status' => TenantStatus::Active]);

    $this->actingAs($admin)
        ->get(route('platform.dashboard'))
        ->assertOk()
        ->assertSee('All clear across InSyte.')
        ->assertDontSee('Suspended Partners');
});

test('platform dashboard active users kpi counts users across channel partners', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'democompany',
        'name' => 'Demo Company',
        'admin_email' => 'owner@demo.test',
    ]);

    $tenant->run(function (): void {
        User::query()->create([
            'name' => 'Demo User Two',
            'email' => 'two@demo.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        User::query()->create([
            'name' => 'Demo User Three',
            'email' => 'three@demo.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    });

    $this->actingAs($admin)
        ->get(route('platform.dashboard'))
        ->assertOk()
        ->assertSee('Active Users')
        ->assertSeeHtml('<p class="mt-0.5 text-2xl font-bold leading-tight tracking-tight text-sky-600">3</p>');
});

test('platform dashboard kpi cards link to relevant modules', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.dashboard'))
        ->assertOk()
        ->assertSee(route('tenants.index', absolute: false), false)
        ->assertSee(route('platform.revenue', absolute: false), false)
        ->assertSee(route('platform.integrations', absolute: false), false)
        ->assertSee(route('platform.analytics', absolute: false), false);
});

test('guests are redirected away from the platform dashboard', function () {
    $this->get(route('platform.dashboard'))->assertRedirect(route('login'));
});

test('non super admins cannot view the platform dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('platform.dashboard'))
        ->assertForbidden();
});
