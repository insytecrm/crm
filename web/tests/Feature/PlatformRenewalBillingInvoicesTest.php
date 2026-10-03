<?php

use App\Actions\CreateRenewalBillingInvoices;
use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\Tenant;

test('renewal invoices are created one day before next billing date', function () {
    $tenant = Tenant::factory()->create(['email' => 'billing@partner.test']);
    $plan = Plan::factory()->create(['price_monthly' => 4999]);

    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 4999,
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->addDay()->startOfDay(),
    ]);

    $created = app(CreateRenewalBillingInvoices::class)->handle(now());

    expect($created)->toBe(1);

    $invoice = BillingInvoice::query()
        ->where('partner_subscription_id', $subscription->id)
        ->whereDate('period_start', now()->addDay()->toDateString())
        ->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(BillingInvoiceStatus::Pending)
        ->and($invoice->billed_to_email)->toBe('billing@partner.test');
});

test('renewal invoice generation is idempotent for the same billing period', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create(['price_monthly' => 4999]);
    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 4999,
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->addDay()->startOfDay(),
    ]);

    $action = app(CreateRenewalBillingInvoices::class);

    expect($action->handle(now()))->toBe(1)
        ->and($action->handle(now()))->toBe(0)
        ->and(BillingInvoice::query()->where('partner_subscription_id', $subscription->id)->count())->toBe(1);
});
