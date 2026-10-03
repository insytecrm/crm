<?php

use App\Enums\BillingInvoiceStatus;
use App\Enums\QuotationStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;

test('super admins can bulk delete draft quotations', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();

    $draft = Quotation::factory()->create(['plan_id' => $plan->id, 'status' => QuotationStatus::Draft]);
    $sent = Quotation::factory()->sent()->create(['plan_id' => $plan->id]);

    $this->actingAs($admin)
        ->from(route('platform.quotations'))
        ->delete(route('platform.quotations.bulk-destroy'), [
            'ids' => [$draft->id, $sent->id],
        ])
        ->assertRedirect(route('platform.quotations'));

    expect(Quotation::query()->find($draft->id))->toBeNull()
        ->and(Quotation::query()->find($sent->id))->not->toBeNull();
});

test('super admins can bulk mark invoices paid and cancel unpaid invoices', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
    ]);

    $pending = BillingInvoice::factory()->forSubscription($subscription)->create([
        'status' => BillingInvoiceStatus::Pending,
        'period_end' => now()->addMonth(),
    ]);
    $overdue = BillingInvoice::factory()->forSubscription($subscription)->create([
        'status' => BillingInvoiceStatus::Overdue,
        'period_end' => now()->addMonth(),
    ]);

    $this->actingAs($admin)
        ->from(route('platform.revenue.invoices'))
        ->post(route('platform.revenue.invoices.bulk-mark-paid'), [
            'ids' => [$pending->id],
        ])
        ->assertRedirect(route('platform.revenue.invoices'));

    expect($pending->refresh()->status)->toBe(BillingInvoiceStatus::Paid);

    $this->actingAs($admin)
        ->from(route('platform.revenue.invoices'))
        ->delete(route('platform.revenue.invoices.bulk-destroy'), [
            'ids' => [$overdue->id],
        ])
        ->assertRedirect(route('platform.revenue.invoices'));

    expect($overdue->refresh()->status)->toBe(BillingInvoiceStatus::Cancelled);
});

test('super admins can bulk delete platform leads', function () {
    $admin = User::factory()->superAdmin()->create();
    $lead = PlatformLead::factory()->create();

    $this->actingAs($admin)
        ->from(route('platform.leads'))
        ->delete(route('platform.leads.bulk-destroy'), [
            'ids' => [$lead->id],
        ])
        ->assertRedirect(route('platform.leads'));

    expect(PlatformLead::query()->find($lead->id))->toBeNull();
});
