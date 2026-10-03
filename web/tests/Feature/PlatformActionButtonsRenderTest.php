<?php

use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;

test('quotations list send action compiles alpine handlers', function () {
    $admin = User::factory()->superAdmin()->create();
    $quotation = Quotation::factory()->sent()->create();

    $html = $this->actingAs($admin)
        ->get(route('platform.quotations'))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('open-send-quotation-list')
        ->toContain('open-send-quotation-list.window')
        ->not->toContain('@js(route')
        ->not->toContain('@js($quotation');
});

test('invoices list send action compiles alpine handlers', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::factory()->create();
    $tenant = Tenant::factory()->create();
    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
    ]);
    BillingInvoice::factory()->forSubscription($subscription)->create();

    $html = $this->actingAs($admin)
        ->get(route('platform.revenue.invoices'))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('open-send-invoice-list')
        ->toContain('open-send-invoice-list.window')
        ->not->toContain('@js(route')
        ->not->toContain('@js($invoice');
});

test('lead detail add note opens the note modal listener', function () {
    $admin = User::factory()->superAdmin()->create();
    $lead = PlatformLead::factory()->create(['owner_id' => $admin->id]);

    $html = $this->actingAs($admin)
        ->get(route('platform.leads.show', $lead))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('open-lead-note')
        ->toContain('add-lead-note')
        ->toContain("x-on:open-lead-note.window=\"\$dispatch('open-modal', 'add-lead-note')\"");
});

test('channel partner shell edit action compiles alpine handlers', function () {
    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'Shell Partner']);

    $html = $this->actingAs($admin)
        ->get(route('tenants.show', $tenant))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('open-edit-partner')
        ->not->toContain('@js((string) $tenant->id)')
        ->not->toContain('@js($tenant');
});
