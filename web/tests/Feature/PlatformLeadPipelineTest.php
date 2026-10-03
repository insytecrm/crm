<?php

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Enums\PlatformLeadStage;
use App\Enums\QuotationStatus;
use App\Enums\SubscriptionStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Platform\QuotationPricing;
use Illuminate\Support\Facades\DB;

test('super admin can start a trial from a lead', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $lead = PlatformLead::factory()->create([
        'company_name' => 'Trial Realty',
        'contact_person' => 'Trial Owner',
        'email' => 'owner@trialrealty.test',
        'phone' => '9888888888',
    ]);

    $this->actingAs($admin)
        ->post(route('platform.leads.start-trial', $lead), [
            'company_name' => 'Trial Realty',
            'email' => 'owner@trialrealty.test',
            'phone' => '9888888888',
            'plan_id' => $plan->id,
            'trial_days' => 7,
            'slug' => 'trialrealty',
        ])
        ->assertRedirect();

    $lead->refresh();
    $tenant = Tenant::query()->find('trialrealty');
    $subscription = PartnerSubscription::query()->where('tenant_id', 'trialrealty')->first();

    expect($lead->stage)->toBe(PlatformLeadStage::Trial)
        ->and($lead->tenant_id)->toBe('trialrealty')
        ->and($tenant)->not->toBeNull()
        ->and($subscription?->status)->toBe(SubscriptionStatus::Trial);

    $tenant->run(function (): void {
        expect(DB::table('users')->where('email', 'owner@trialrealty.test')->exists())->toBeTrue();
    });
});

test('direct lead follows quote pay onboard path', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $plan->update(['price_monthly' => 4999]);

    $lead = PlatformLead::factory()->create([
        'company_name' => 'Paid Realty',
        'contact_person' => 'Paid Owner',
        'email' => 'owner@paidrealty.test',
        'phone' => '9888888888',
        'stage' => PlatformLeadStage::Quoted,
    ]);

    $quotation = Quotation::factory()->sent()->create([
        'platform_lead_id' => $lead->id,
        'company_name' => $lead->company_name,
        'owner_name' => $lead->contact_person,
        'email' => $lead->email,
        'phone' => $lead->phone,
        'plan_id' => $plan->id,
        'billing_cycle' => BillingCycle::Monthly,
        'plan_price' => 4999,
        'discount_amount' => 0,
        'tax_amount' => 900,
        'total' => 5899,
        'tenant_id' => null,
    ]);

    $this->actingAs($admin)
        ->post(route('platform.quotations.accept', $quotation))
        ->assertRedirect();

    $invoice = BillingInvoice::query()->where('quotation_id', $quotation->id)->first();

    expect($invoice)->not->toBeNull()
        ->and($lead->canOnboard())->toBeFalse();

    $this->actingAs($admin)
        ->post(route('platform.revenue.invoices.mark-paid', $invoice))
        ->assertRedirect();

    expect($lead->refresh()->stage)->toBe(PlatformLeadStage::Paid)
        ->and($lead->canOnboard())->toBeTrue()
        ->and($lead->canActivateSubscription())->toBeFalse();

    $this->actingAs($admin)
        ->post(route('platform.leads.onboard', $lead), [
            'admin_name' => 'Paid Owner',
            'admin_email' => 'admin@paidrealty.test',
            'slug' => 'paidrealty',
        ])
        ->assertRedirect(route('platform.leads.show', $lead));

    $lead->refresh();
    $tenant = Tenant::query()->find('paidrealty');
    $subscription = PartnerSubscription::query()->where('tenant_id', 'paidrealty')->first();

    expect($lead->stage)->toBe(PlatformLeadStage::Live)
        ->and($lead->onboarded_at)->not->toBeNull()
        ->and($tenant)->not->toBeNull()
        ->and($subscription?->status)->toBe(SubscriptionStatus::Active);
});

test('trial lead activates paid subscription on existing workspace after payment', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $plan->update(['price_monthly' => 4999]);

    $lead = PlatformLead::factory()->create([
        'company_name' => 'Upgrade Realty',
        'contact_person' => 'Upgrade Owner',
        'email' => 'owner@upgraderealty.test',
        'stage' => PlatformLeadStage::Trial,
    ]);

    $this->actingAs($admin)
        ->post(route('platform.leads.start-trial', $lead), [
            'company_name' => 'Upgrade Realty',
            'email' => 'owner@upgraderealty.test',
            'plan_id' => $plan->id,
            'trial_days' => 7,
            'slug' => 'upgraderealty',
        ])
        ->assertRedirect();

    $lead->refresh();
    $trialTenantId = $lead->tenant_id;
    $tenantCountAfterTrialStarted = Tenant::query()->count();

    $quotation = Quotation::factory()->sent()->create([
        'platform_lead_id' => $lead->id,
        'company_name' => $lead->company_name,
        'owner_name' => $lead->contact_person,
        'email' => $lead->email,
        'plan_id' => $plan->id,
        'billing_cycle' => BillingCycle::Monthly,
        'plan_price' => 4999,
        'discount_amount' => 0,
        'tax_amount' => 900,
        'total' => 5899,
        'tenant_id' => $lead->tenant_id,
    ]);

    $this->actingAs($admin)->post(route('platform.quotations.accept', $quotation))->assertRedirect();
    $invoice = BillingInvoice::query()->where('quotation_id', $quotation->id)->first();
    $this->actingAs($admin)->post(route('platform.revenue.invoices.mark-paid', $invoice))->assertRedirect();

    $lead->refresh();
    expect($lead->canActivateSubscription())->toBeTrue()
        ->and($lead->canOnboard())->toBeFalse();

    $this->actingAs($admin)
        ->post(route('platform.leads.activate-subscription', $lead), [
            'rera_number' => 'RERA-1',
            'gst_number' => 'GST-1',
        ])
        ->assertRedirect(route('platform.leads.show', $lead));

    $subscription = PartnerSubscription::query()->where('tenant_id', $lead->tenant_id)->first();

    expect($lead->refresh()->stage)->toBe(PlatformLeadStage::Live)
        ->and($subscription?->status)->toBe(SubscriptionStatus::Active)
        ->and($lead->onboarded_at)->not->toBeNull()
        ->and($lead->tenant_id)->toBe($trialTenantId)
        ->and(Tenant::query()->count())->toBe($tenantCountAfterTrialStarted)
        ->and(PartnerSubscription::query()->where('tenant_id', $trialTenantId)->count())->toBe(1);
});

test('super admin can end an active trial without deleting the partner', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $lead = PlatformLead::factory()->create();

    $this->actingAs($admin)
        ->post(route('platform.leads.start-trial', $lead), [
            'company_name' => $lead->company_name,
            'email' => $lead->email,
            'plan_id' => $plan->id,
            'trial_days' => 7,
            'slug' => 'endedtrial',
        ])
        ->assertRedirect();

    $lead->refresh();

    $this->actingAs($admin)
        ->post(route('platform.leads.end-trial', $lead))
        ->assertRedirect();

    expect(Tenant::query()->find('endedtrial'))->not->toBeNull()
        ->and($lead->refresh()->latestPartnerSubscription()?->status)->toBe(SubscriptionStatus::TrialEnded);
});

test('second paid invoice moves lead to retention', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $lead = PlatformLead::factory()->create(['stage' => PlatformLeadStage::Live]);

    $quotation = Quotation::factory()->accepted()->create([
        'platform_lead_id' => $lead->id,
        'plan_id' => $plan->id,
        'billing_cycle' => BillingCycle::Monthly,
    ]);

    $first = BillingInvoice::factory()->create([
        'quotation_id' => $quotation->id,
        'tenant_id' => $lead->tenant_id,
        'status' => BillingInvoiceStatus::Paid,
        'paid_at' => now()->subMonth(),
    ]);

    $second = BillingInvoice::factory()->create([
        'quotation_id' => $quotation->id,
        'tenant_id' => $lead->tenant_id,
        'status' => BillingInvoiceStatus::Pending,
        'total' => 5899,
    ]);

    expect($lead->refresh()->stage)->toBe(PlatformLeadStage::Live);

    $this->actingAs($admin)
        ->post(route('platform.revenue.invoices.mark-paid', $second))
        ->assertRedirect();

    expect($lead->refresh()->stage)->toBe(PlatformLeadStage::Retention);
});

test('creating a quotation from a lead stores rera and gst without trial fields', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $lead = PlatformLead::factory()->create([
        'company_name' => 'Modal Realty',
        'contact_person' => 'Modal Owner',
        'email' => 'owner@modalrealty.test',
    ]);
    $pricing = QuotationPricing::calculate(4999, 0);

    $this->actingAs($admin)
        ->post(route('platform.quotations.modal.store'), [
            'platform_lead_id' => $lead->id,
            'company_name' => $lead->company_name,
            'owner_name' => $lead->contact_person,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'rera_number' => 'RERA123',
            'gst_number' => 'GST123',
            'plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
            'plan_price' => $pricing['plan_price'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount' => $pricing['tax_amount'],
            'valid_until' => now()->addDays(7)->toDateString(),
            '_quotation_wizard' => '1',
        ])
        ->assertRedirect();

    $quotation = Quotation::query()->where('platform_lead_id', $lead->id)->first();

    expect($quotation)->not->toBeNull()
        ->and($quotation->trial_enabled)->toBeFalse()
        ->and($quotation->rera_number)->toBe('RERA123')
        ->and($quotation->gst_number)->toBe('GST123')
        ->and($quotation->status)->toBe(QuotationStatus::Draft);
});
