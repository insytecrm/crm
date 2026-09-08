<?php

use App\Actions\CreateTenant;
use App\Actions\ResumePartnerSubscription;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\PartnerSubscription;
use App\Models\Tenant;
use App\Models\User;

test('an unknown company slug returns 404', function () {
    $this->get('/missing/login')->assertNotFound();
});

test('the tenant login page can be rendered', function () {
    Tenant::factory()->create([
        'id' => 'acme',
        'name' => 'Acme Inc',
    ]);

    $this->get('/acme/login')
        ->assertSee('Welcome Back')
        ->assertSee('Sign in to Acme Inc')
        ->assertSee('Sign In')
        ->assertSee('/images/login-hero.jpg')
        ->assertSee('/images/2.png')
        ->assertSee('alt="InSyte CRM"', false)
        ->assertDontSee('Choose your role');
});

test('the tenant dashboard renders the sidebar navigation', function () {
    app(CreateTenant::class)->handle([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ]);

    $this->post('/acme/login', [
        'email' => 'admin@acme.test',
        'password' => 'password',
    ]);

    $this->get('/acme/dashboard')
        ->assertSee('Dashboard')
        ->assertSee('Leads')
        ->assertDontSee('>Acme Inc</', false)
        ->assertSee('uiPopover', false)
        ->assertSee('rounded-2xl', false)
        ->assertSee('rounded-full', false)
        ->assertSee('Log Out');
});

test('the tenant dashboard displays the InSyte logo', function () {
    app(CreateTenant::class)->handle([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ]);

    $this->post('/acme/login', [
        'email' => 'admin@acme.test',
        'password' => 'password',
    ]);

    $this->get('/acme/dashboard')
        ->assertSee('/images/'.rawurlencode('inSyte (2).png'))
        ->assertSee('/images/5.png')
        ->assertSee('alt="InSyte CRM"', false);
});

test('company users can authenticate on their tenant login screen', function () {
    app(CreateTenant::class)->handle([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'email' => 'office@acme.test',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ]);

    $this->get('/acme/login')->assertOk();

    $this->post('/acme/login', [
        'email' => 'admin@acme.test',
        'password' => 'password',
    ])->assertRedirect(route('tenant.dashboard', ['tenant' => 'acme']));

    $this->get('/acme/dashboard')->assertOk();
});

test('company users cannot authenticate with an invalid password', function () {
    app(CreateTenant::class)->handle([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ]);

    $this->post('/acme/login', [
        'email' => 'admin@acme.test',
        'password' => 'wrong-password',
    ]);

    $this->get('/acme/dashboard')->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('suspended companies cannot be accessed', function () {
    app(CreateTenant::class)->handle([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'status' => TenantStatus::Suspended->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ]);

    $this->get('/acme/login')->assertForbidden();
});

test('companies with a paused subscription cannot be accessed until resumed', function () {
    app(CreateTenant::class)->handle([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ]);

    $tenant = Tenant::query()->findOrFail('acme');
    $subscription = PartnerSubscription::factory()->paused()->create([
        'tenant_id' => $tenant->id,
        'next_billing_at' => now()->addDays(12),
    ]);
    $nextBillingAt = $subscription->next_billing_at?->toISOString();

    $this->get('/acme/login')->assertForbidden();

    app(ResumePartnerSubscription::class)->handle($subscription);

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->fresh()->next_billing_at?->toISOString())->toBe($nextBillingAt);

    $this->get('/acme/login')->assertOk();

    $this->post('/acme/login', [
        'email' => 'admin@acme.test',
        'password' => 'password',
    ]);

    $this->get('/acme/dashboard')->assertOk();
});

test('platform super admins cannot use a tenant login', function () {
    User::factory()->superAdmin()->create([
        'email' => 'admin@platform.com',
        'password' => 'password',
    ]);

    app(CreateTenant::class)->handle([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ]);

    $this->post('/acme/login', [
        'email' => 'admin@platform.com',
        'password' => 'password',
    ]);

    $this->get('/acme/dashboard')->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});
