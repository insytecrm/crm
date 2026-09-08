<?php

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Enums\QuotationStatus;
use App\Enums\SubscriptionStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Platform\QuotationPricing;
use Illuminate\Support\Facades\DB;

test('quotation create entry opens the modal on the quotations list', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.quotations.create'))
        ->assertRedirect(route('platform.quotations', ['quote' => 1]));

    $this->actingAs($admin)
        ->get(route('platform.quotations', ['quote' => 1]))
        ->assertOk()
        ->assertSee('Create Quotation')
        ->assertSee('create-quotation')
        ->assertSee('quotationCreateWizard');
});

test('super admins can view quotations index without mock data', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.quotations'))
        ->assertOk()
        ->assertSee('Quotations')
        ->assertSee('Manage commercial proposals sent to Channel Partners.')
        ->assertSee('Total Quotations')
        ->assertSee('No quotations match these filters.')
        ->assertDontSee('Coming soon')
        ->assertDontSee('QT-1023')
        ->assertDontSee('ABC Realty');
});

test('non super admins cannot view quotations', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('platform.quotations'))
        ->assertForbidden();
});

test('super admins can create a draft quotation from the popup modal', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $pricing = QuotationPricing::calculate(4999, 0);

    $this->actingAs($admin)
        ->from(route('tenants.index'))
        ->post(route('platform.quotations.modal.store'), [
            'company_name' => 'Modal Realty',
            'owner_name' => 'Modal Owner',
            'email' => 'owner@modalrealty.test',
            'phone' => '9888888888',
            'plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
            'trial_enabled' => 1,
            'trial_days' => 7,
            'plan_price' => $pricing['plan_price'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount' => $pricing['tax_amount'],
            'valid_until' => now()->addDays(7)->toDateString(),
            '_quotation_wizard' => '1',
        ])
        ->assertRedirect();

    $quotation = Quotation::query()->where('company_name', 'Modal Realty')->first();

    expect($quotation)->not->toBeNull()
        ->and($quotation->status)->toBe(QuotationStatus::Draft)
        ->and($quotation->tenant_id)->toBeNull()
        ->and($quotation->email)->toBe('owner@modalrealty.test');
});

test('super admins can create a draft quotation for a prospect through the wizard', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $plan->update([
        'price_monthly' => 4999,
        'price_annual' => 49990,
        'trial_enabled' => true,
        'trial_days' => 7,
    ]);

    $this->actingAs($admin)
        ->post(route('platform.quotations.wizard.prospect.store'), [
            'company_name' => 'ABC Realty',
            'owner_name' => 'Rahul Sharma',
            'email' => 'rahul@abcrealty.com',
            'phone' => '9999999999',
        ])
        ->assertRedirect(route('platform.quotations.wizard.plan'));

    $this->actingAs($admin)
        ->post(route('platform.quotations.wizard.plan.store'), [
            'plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
            'trial_enabled' => 1,
            'trial_days' => 7,
        ])
        ->assertRedirect(route('platform.quotations.wizard.pricing'));

    $pricing = QuotationPricing::calculate(4999, 0);

    $this->actingAs($admin)
        ->post(route('platform.quotations.wizard.pricing.store'), [
            'plan_price' => $pricing['plan_price'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount' => $pricing['tax_amount'],
        ])
        ->assertRedirect(route('platform.quotations.wizard.review'));

    $validUntil = now()->addDays(7)->toDateString();

    $response = $this->actingAs($admin)
        ->post(route('platform.quotations.store'), [
            'valid_until' => $validUntil,
        ]);

    $quotation = Quotation::query()->first();

    expect($quotation)->not->toBeNull()
        ->and($quotation->status)->toBe(QuotationStatus::Draft)
        ->and($quotation->tenant_id)->toBeNull()
        ->and($quotation->company_name)->toBe('ABC Realty')
        ->and($quotation->owner_name)->toBe('Rahul Sharma')
        ->and($quotation->email)->toBe('rahul@abcrealty.com')
        ->and($quotation->plan_id)->toBe($plan->id)
        ->and($quotation->total)->toBe($pricing['total'])
        ->and($quotation->valid_until->toDateString())->toBe($validUntil);

    $response->assertRedirect(route('platform.quotations.show', $quotation));
});

test('accepted quotation onboarding creates partner subscription invoice and handover login', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $plan->update(['price_monthly' => 4999]);

    $quotation = Quotation::factory()->accepted()->create([
        'company_name' => 'ABC Realty',
        'owner_name' => 'Rahul Sharma',
        'email' => 'rahul@abcrealty.test',
        'phone' => '9999999999',
        'plan_id' => $plan->id,
        'billing_cycle' => BillingCycle::Monthly,
        'plan_price' => 4999,
        'discount_amount' => 0,
        'tax_amount' => 900,
        'total' => 5899,
        'trial_enabled' => true,
        'trial_days' => 7,
        'tenant_id' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('platform.quotations.onboard', $quotation))
        ->assertOk()
        ->assertSee('Start Onboarding')
        ->assertSee('ABC Realty');

    $response = $this->actingAs($admin)
        ->post(route('platform.quotations.onboard.store', $quotation), [
            'admin_name' => 'Rahul Sharma',
            'admin_email' => 'admin@abcrealty.test',
            'slug' => 'abcrealty',
        ])
        ->assertRedirect(route('platform.quotations.show', $quotation));

    $quotation->refresh();
    $tenant = Tenant::query()->find('abcrealty');
    $subscription = PartnerSubscription::query()->whereKey($quotation->partner_subscription_id)->first();
    $invoice = BillingInvoice::query()->where('partner_subscription_id', $subscription?->id)->first();

    expect($tenant)->not->toBeNull()
        ->and($tenant->name)->toBe('ABC Realty')
        ->and($tenant->plan_key)->toBe('growth')
        ->and($quotation->tenant_id)->toBe('abcrealty')
        ->and($quotation->onboarded_at)->not->toBeNull()
        ->and($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe(SubscriptionStatus::Trial)
        ->and($subscription->amount)->toBe(4999)
        ->and($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(BillingInvoiceStatus::Pending)
        ->and($invoice->total)->toBe(5899);

    $tenant->run(function () {
        expect(DB::table('users')->where('email', 'admin@abcrealty.test')->exists())->toBeTrue();
    });

    $response->assertSessionHas('quotation_handover');
    $handover = session('quotation_handover');

    expect($handover['admin_email'])->toBe('admin@abcrealty.test')
        ->and($handover['admin_password'])->not->toBeEmpty()
        ->and($handover['login_url'])->not->toBeEmpty();

    $this->actingAs($admin)
        ->get(route('platform.quotations.show', $quotation))
        ->assertOk()
        ->assertSee('Handover login details')
        ->assertSee('admin@abcrealty.test')
        ->assertSee($handover['admin_password']);
});

test('super admins can send and accept prospect quotations before onboarding', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();

    $quotation = Quotation::factory()->create([
        'plan_id' => $plan->id,
        'company_name' => 'Prime Realty',
        'owner_name' => 'Owner',
        'email' => 'owner@prime.test',
        'status' => QuotationStatus::Draft,
        'tenant_id' => null,
    ]);

    $this->actingAs($admin)
        ->post(route('platform.quotations.send', $quotation))
        ->assertRedirect(route('platform.quotations.show', $quotation));

    expect($quotation->refresh()->status)->toBe(QuotationStatus::Sent);

    $this->actingAs($admin)
        ->post(route('platform.quotations.accept', $quotation))
        ->assertRedirect(route('platform.quotations.show', $quotation));

    expect($quotation->refresh()->status)->toBe(QuotationStatus::Accepted)
        ->and($quotation->canStartOnboarding())->toBeTrue()
        ->and($quotation->canCreateSubscription())->toBeFalse();
});

test('only draft quotations can be edited and duplicates stay prospect-only', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();

    $draft = Quotation::factory()->create([
        'plan_id' => $plan->id,
        'status' => QuotationStatus::Draft,
    ]);

    $sent = Quotation::factory()->sent()->create([
        'plan_id' => $plan->id,
        'company_name' => 'XYZ Properties',
    ]);

    $this->actingAs($admin)
        ->get(route('platform.quotations.edit', $draft))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('platform.quotations.edit', $sent))
        ->assertRedirect(route('platform.quotations.show', $sent));

    $this->actingAs($admin)
        ->post(route('platform.quotations.duplicate', $sent))
        ->assertRedirect();

    $copy = Quotation::query()->where('status', QuotationStatus::Draft)->whereKeyNot($draft->id)->latest('id')->first();

    expect($copy)->not->toBeNull()
        ->and($copy->company_name)->toBe('XYZ Properties')
        ->and($copy->tenant_id)->toBeNull()
        ->and($copy->number)->not->toBe($sent->number);
});

test('expired quotations due past valid until are marked expired on index', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $quotation = Quotation::factory()->sent()->create([
        'plan_id' => $plan->id,
        'valid_until' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->get(route('platform.quotations'))
        ->assertOk()
        ->assertSee($quotation->number);

    expect($quotation->refresh()->status)->toBe(QuotationStatus::Expired);
});

test('quotation download returns live quotation content', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'pro')->firstOrFail();
    $quotation = Quotation::factory()->create([
        'plan_id' => $plan->id,
        'number' => 'QT-2501',
        'company_name' => 'Prime Realty',
    ]);

    $this->actingAs($admin)
        ->get(route('platform.quotations.download', $quotation))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename="QT-2501.txt"')
        ->assertSee('QT-2501')
        ->assertSee('Prime Realty')
        ->assertSee('Pro');
});
