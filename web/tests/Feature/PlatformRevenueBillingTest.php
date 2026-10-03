<?php

use App\Enums\BillingInvoiceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\BillingDiscount;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\BillingRefund;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;

test('super admins can view the revenue overview without mock figures', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.revenue'))
        ->assertOk()
        ->assertSee('Revenue & Billing')
        ->assertSee('Track InSyte revenue, subscriptions, invoices, and payments.')
        ->assertSee('Total Revenue')
        ->assertSee('Collected')
        ->assertSee('Pending')
        ->assertSee('Overdue')
        ->assertDontSee('Coming soon')
        ->assertDontSee('₹4,82,500');
});

test('non super admins cannot view revenue billing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('platform.revenue'))
        ->assertForbidden();
});

test('super admins can browse billing sections backed by live records', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'ABC Realty']);
    $plan = Plan::factory()->create(['name' => 'Growth', 'price_monthly' => 4999]);

    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'amount' => 4999,
        'status' => SubscriptionStatus::Active,
    ]);

    $invoice = BillingInvoice::factory()->forSubscription($subscription)->create([
        'number' => 'INV-1023',
        'status' => BillingInvoiceStatus::Paid,
        'total' => 4999,
        'issued_at' => now(),
    ]);

    $payment = BillingPayment::factory()->forInvoice($invoice)->create([
        'status' => BillingPaymentStatus::Paid,
        'payment_date' => now(),
        'amount' => 4999,
    ]);

    BillingDiscount::factory()->forInvoice($invoice)->create([
        'amount' => 500,
        'reason' => 'Launch Offer',
        'applied_by' => $admin->id,
    ]);

    BillingRefund::factory()->forPayment($payment)->create([
        'amount' => 4999,
        'reason' => 'Subscription cancellation',
    ]);

    $this->actingAs($admin)
        ->get(route('platform.revenue.subscriptions'))
        ->assertOk()
        ->assertSee('ABC Realty')
        ->assertSee('Growth');

    $this->actingAs($admin)
        ->get(route('platform.revenue.subscriptions.show', $subscription))
        ->assertOk()
        ->assertSee('ABC Realty')
        ->assertSee('#INV-1023');

    $this->actingAs($admin)
        ->get(route('platform.revenue.invoices'))
        ->assertOk()
        ->assertSee('#INV-1023')
        ->assertSee('ABC Realty');

    $this->actingAs($admin)
        ->get(route('platform.revenue.invoices.show', $invoice))
        ->assertOk()
        ->assertSee('#INV-1023')
        ->assertSee('Invoice Timeline');

    $this->actingAs($admin)
        ->get(route('platform.revenue.payments'))
        ->assertOk()
        ->assertSee('ABC Realty');

    $this->actingAs($admin)
        ->get(route('platform.revenue.payments.show', $payment))
        ->assertOk()
        ->assertSee('Technical Details')
        ->assertSee('#INV-1023');

    $this->actingAs($admin)
        ->get(route('platform.revenue.adjustments'))
        ->assertOk()
        ->assertSee('Discounts')
        ->assertSee('Refunds');

    $this->actingAs($admin)
        ->get(route('platform.revenue.adjustments.discounts'))
        ->assertOk()
        ->assertSee('Launch Offer')
        ->assertSee('ABC Realty');

    $this->actingAs($admin)
        ->get(route('platform.revenue.adjustments.refunds'))
        ->assertOk()
        ->assertSee('Subscription cancellation')
        ->assertSee('ABC Realty');
});

test('super admins can mark an invoice paid and pause a subscription', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();

    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
    ]);

    $invoice = BillingInvoice::factory()->forSubscription($subscription)->pending()->create([
        'number' => 'INV-2001',
        'total' => 5999,
    ]);

    $this->actingAs($admin)
        ->post(route('platform.revenue.invoices.mark-paid', $invoice))
        ->assertRedirect();

    expect($invoice->fresh()->status)->toBe(BillingInvoiceStatus::Paid)
        ->and($invoice->payments()->where('status', BillingPaymentStatus::Paid)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->post(route('platform.revenue.subscriptions.pause', $subscription))
        ->assertRedirect();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Paused);

    $this->actingAs($admin)
        ->post(route('platform.revenue.subscriptions.resume', $subscription))
        ->assertRedirect();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->fresh()->paused_at)->toBeNull();
});

test('revenue overview export downloads a csv', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.revenue.export'))
        ->assertOk()
        ->assertHeader('content-disposition');
});
