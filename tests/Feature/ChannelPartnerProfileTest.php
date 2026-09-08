<?php

use App\Actions\IssueTenantLeadApiToken;
use App\Enums\BillingInvoiceStatus;
use App\Enums\PropertyPortal;
use App\Enums\SubscriptionStatus;
use App\Models\Automation;
use App\Models\BillingDiscount;
use App\Models\BillingInvoice;
use App\Models\GoogleSheetConnection;
use App\Models\Lead;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\PortalWebhookEndpoint;
use App\Models\Tenant;
use App\Models\User;

test('super admins can open partner overview shell and tabs', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'abcrealty',
        'name' => 'ABC Realty',
        'admin_name' => 'Rahul Sharma',
        'admin_email' => 'rahul@abcrealty.test',
        'owner_name' => 'Rahul Sharma',
        'plan_key' => 'growth',
        'billing_cycle' => 'monthly',
    ]);

    $this->actingAs($admin)
        ->get(route('tenants.show', $tenant))
        ->assertOk()
        ->assertSee('ABC Realty')
        ->assertSee('Channel Partners')
        ->assertSee('Access Workspace')
        ->assertSee('Overview')
        ->assertSee('Users')
        ->assertSee('Subscription')
        ->assertSee('Usage')
        ->assertSee('Integrations')
        ->assertSee('Activity')
        ->assertSee('Account Snapshot')
        ->assertSee('Usage Snapshot')
        ->assertSee('Integration Snapshot')
        ->assertSee('Recent Activity')
        ->assertSee('Growth')
        ->assertDontSee('InSyte AI OS')
        ->assertDontSee('Priority Leads');
});

test('partner users tab lists tenant users', function () {
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
        ->assertSee('Users')
        ->assertSee('Rahul Sharma')
        ->assertSee('rahul@abcrealty.test')
        ->assertSee('Search name or email');
});

test('partner profile shows live tenant usage and integrations', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'democompany',
        'name' => 'Demo Company',
        'admin_name' => 'Demo Admin',
        'admin_email' => 'owner@demo.test',
        'plan_key' => 'growth',
        'billing_cycle' => 'monthly',
    ]);

    // Settings visits can issue tokens/webhooks; without real usage they stay Not Connected.
    app(IssueTenantLeadApiToken::class)->handle($tenant);
    $tenant->refresh();

    PortalWebhookEndpoint::factory()->create([
        'tenant_id' => $tenant->id,
        'portal' => PropertyPortal::Housing,
        'is_active' => true,
        'last_used_at' => null,
    ]);

    PortalWebhookEndpoint::factory()->create([
        'tenant_id' => $tenant->id,
        'portal' => PropertyPortal::NinetyNineAcres,
        'is_active' => true,
        'last_used_at' => null,
    ]);

    $tenant->run(function (): void {
        $owner = User::query()->where('email', 'owner@demo.test')->firstOrFail();

        Lead::factory()->count(3)->create([
            'assigned_to_id' => $owner->id,
            'created_by_id' => $owner->id,
        ]);
        Automation::factory()->count(2)->active()->create(['user_id' => $owner->id]);
        GoogleSheetConnection::factory()->connected()->create([
            'created_by_id' => $owner->id,
            'name' => 'Inbound Leads Sheet',
            'last_synced_at' => now()->subMinutes(12),
        ]);
    });

    $this->actingAs($admin)
        ->get(route('tenants.show', $tenant))
        ->assertOk()
        ->assertSee('Lead API')
        ->assertSee('Google Sheets')
        ->assertSee('Facebook Lead Ads')
        ->assertSee('Housing.com')
        ->assertSee('Automations');

    $this->actingAs($admin)
        ->get(route('tenants.usage', $tenant))
        ->assertOk()
        ->assertSee('Plan Usage')
        ->assertSee('3')
        ->assertSee('2');

    $integrations = $this->actingAs($admin)
        ->get(route('tenants.integrations', $tenant))
        ->assertOk()
        ->assertSee('Lead API')
        ->assertSee('Google Sheets')
        ->assertSee('Facebook Lead Ads')
        ->assertSee('99acres')
        ->assertSee('Housing.com')
        ->assertSee('MagicBricks')
        ->assertSee('NoBroker')
        ->assertSee('WhatsApp')
        ->assertSee('Email')
        ->assertSee('Calendar')
        ->assertSee('Coming soon')
        ->assertSee('1 Connected');

    expect($integrations->getContent())
        ->toMatch('/Google Sheets[\s\S]*?Connected/')
        ->toMatch('/Lead API[\s\S]*?Not Connected/')
        ->toMatch('/Housing\.com[\s\S]*?Not Connected/')
        ->toMatch('/99acres[\s\S]*?Not Connected/')
        ->toMatch('/Facebook Lead Ads[\s\S]*?Not Connected/');

    $this->actingAs($admin)
        ->get(route('tenants.activity', $tenant))
        ->assertOk()
        ->assertSee('Google Sheets synced: Inbound Leads Sheet');
});

test('partner subscription usage integrations and activity tabs render', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant([
        'slug' => 'abcrealty',
        'name' => 'ABC Realty',
        'plan_key' => 'growth',
        'billing_cycle' => 'monthly',
    ]);
    $plan = Plan::query()->where('key', 'growth')->first() ?? Plan::factory()->create(['key' => 'growth', 'name' => 'Growth']);
    PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'amount' => 4999,
        'status' => SubscriptionStatus::Active,
    ]);

    $this->actingAs($admin)->get(route('tenants.subscription', $tenant))
        ->assertOk()
        ->assertSee('Subscription')
        ->assertSee('Billing History')
        ->assertSee('Change Plan')
        ->assertSee('Extend Trial')
        ->assertSee('Apply Discount')
        ->assertSee('Pause')
        ->assertSee('Cancel')
        ->assertDontSee('pointer-events-none w-full justify-center opacity-70', false);

    $this->actingAs($admin)->get(route('tenants.usage', $tenant))
        ->assertOk()
        ->assertSee('Plan Usage')
        ->assertSee('Activity This Month');

    $this->actingAs($admin)->get(route('tenants.integrations', $tenant))
        ->assertOk()
        ->assertSee('WhatsApp')
        ->assertSee('Google Sheets')
        ->assertSee('Calendar');

    $this->actingAs($admin)->get(route('tenants.activity', $tenant))
        ->assertOk()
        ->assertSee('All Activity')
        ->assertSee('ABC Realty onboarded');
});

test('super admins can pause and apply discount from partner subscription tab', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = createTestTenant(['slug' => 'abcrealty', 'name' => 'ABC Realty']);
    $plan = Plan::factory()->create(['name' => 'Growth', 'price_monthly' => 4999]);
    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'amount' => 4999,
        'status' => SubscriptionStatus::Active,
    ]);
    $invoice = BillingInvoice::factory()->forSubscription($subscription)->create([
        'status' => BillingInvoiceStatus::Pending,
        'subtotal' => 4999,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total' => 4999,
        'paid_at' => null,
    ]);

    $this->actingAs($admin)
        ->from(route('tenants.subscription', $tenant))
        ->post(route('platform.revenue.subscriptions.discount', $subscription), [
            'amount' => 500,
            'reason' => 'Partner goodwill',
        ])
        ->assertRedirect(route('tenants.subscription', $tenant))
        ->assertSessionHas('status');

    expect($invoice->fresh()->discount_amount)->toBe(500)
        ->and($invoice->fresh()->total)->toBe(4499)
        ->and(BillingDiscount::query()->where('tenant_id', $tenant->id)->where('reason', 'Partner goodwill')->exists())->toBeTrue();

    $this->actingAs($admin)
        ->from(route('tenants.subscription', $tenant))
        ->post(route('platform.revenue.subscriptions.pause', $subscription))
        ->assertRedirect(route('tenants.subscription', $tenant));

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Paused);

    $this->actingAs($admin)
        ->get(route('tenants.subscription', $tenant))
        ->assertOk()
        ->assertSee('Resume')
        ->assertDontSee(route('platform.revenue.subscriptions.pause', $subscription), false);

    $nextBillingAt = $subscription->fresh()->next_billing_at?->toISOString();

    $this->actingAs($admin)
        ->from(route('tenants.subscription', $tenant))
        ->post(route('platform.revenue.subscriptions.resume', $subscription))
        ->assertRedirect(route('tenants.subscription', $tenant));

    $resumed = $subscription->fresh();

    expect($resumed->status)->toBe(SubscriptionStatus::Active)
        ->and($resumed->paused_at)->toBeNull()
        ->and($resumed->next_billing_at?->toISOString())->toBe($nextBillingAt);
});

test('non super admins cannot open partner profile tabs', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($user)->get(route('tenants.show', $tenant))->assertForbidden();
    $this->actingAs($user)->get(route('tenants.users', $tenant))->assertForbidden();
});
