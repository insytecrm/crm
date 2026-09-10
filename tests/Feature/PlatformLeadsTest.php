<?php

use App\Enums\BillingCycle;
use App\Enums\PlatformLeadStage;
use App\Enums\QuotationStatus;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Models\User;
use App\Support\Platform\QuotationPricing;

test('super admins can view leads index', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.leads'))
        ->assertOk()
        ->assertSee('Leads')
        ->assertSee('Manage your InSyte customer journey from enquiry to retention.')
        ->assertSee('Total Leads')
        ->assertSee('No leads match these filters.')
        ->assertSee('add-platform-lead');
});

test('add lead entry opens the modal on the leads list', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.leads.create'))
        ->assertRedirect(route('platform.leads', ['add' => 1]));

    $this->actingAs($admin)
        ->get(route('platform.leads', ['add' => 1]))
        ->assertOk()
        ->assertSee('Add Lead')
        ->assertSee('add-platform-lead');
});

test('add lead validation errors reopen the modal on the leads list', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->from(route('platform.leads'))
        ->post(route('platform.leads.store'), [
            '_add_lead_modal' => '1',
            'company_name' => '',
            'contact_person' => 'Rahul Sharma',
            'email' => 'not-an-email',
            'phone' => '+91 99999 99999',
        ])
        ->assertRedirect(route('platform.leads'))
        ->assertSessionHasErrors(['company_name', 'email']);

    $this->actingAs($admin)
        ->get(route('platform.leads'))
        ->assertOk()
        ->assertSee('add-platform-lead')
        ->assertSee('Add Lead');
});

test('non super admins cannot view leads', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('platform.leads'))
        ->assertForbidden();
});

test('super admins can create a lead and land on the detail page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('platform.leads.store'), [
            'company_name' => 'ABC Realty',
            'contact_person' => 'Rahul Sharma',
            'email' => 'rahul@abcrealty.com',
            'phone' => '+91 99999 99999',
            'location' => 'Pune',
            'source' => 'website',
            'owner_id' => $admin->id,
        ])
        ->assertRedirect();

    $lead = PlatformLead::query()->where('company_name', 'ABC Realty')->first();

    expect($lead)->not->toBeNull()
        ->and($lead->stage)->toBe(PlatformLeadStage::NewLead)
        ->and($lead->activities()->count())->toBe(1);

    $this->actingAs($admin)
        ->get(route('platform.leads.show', $lead))
        ->assertOk()
        ->assertSee('ABC Realty')
        ->assertSee('Rahul Sharma')
        ->assertSee('Pipeline');
});

test('super admins can update lead stage from the table', function () {
    $admin = User::factory()->superAdmin()->create();
    $lead = PlatformLead::factory()->create([
        'owner_id' => $admin->id,
        'stage' => PlatformLeadStage::NewLead,
    ]);

    $this->actingAs($admin)
        ->from(route('platform.leads'))
        ->patch(route('platform.leads.stage.update', $lead), [
            'stage' => PlatformLeadStage::Contacted->value,
        ])
        ->assertRedirect(route('platform.leads'));

    expect($lead->refresh()->stage)->toBe(PlatformLeadStage::Contacted);
});

test('super admins can add notes to a lead', function () {
    $admin = User::factory()->superAdmin()->create();
    $lead = PlatformLead::factory()->create(['owner_id' => $admin->id]);

    $this->actingAs($admin)
        ->from(route('platform.leads.show', $lead))
        ->post(route('platform.leads.notes.store', $lead), [
            'body' => 'Rahul requested demo for 12 Sep.',
        ])
        ->assertRedirect(route('platform.leads.show', $lead));

    expect($lead->notes()->count())->toBe(1)
        ->and($lead->activities()->where('type', 'note_added')->exists())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('platform.leads.show', $lead))
        ->assertSee('Rahul requested demo for 12 Sep.');
});

test('creating a quotation from a lead links it and advances the stage', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $lead = PlatformLead::factory()->create([
        'owner_id' => $admin->id,
        'company_name' => 'Modal Realty',
        'contact_person' => 'Modal Owner',
        'email' => 'owner@modalrealty.test',
        'phone' => '9888888888',
        'stage' => PlatformLeadStage::DemoCompleted,
    ]);
    $pricing = QuotationPricing::calculate(4999, 0);

    $this->actingAs($admin)
        ->post(route('platform.quotations.modal.store'), [
            'platform_lead_id' => $lead->id,
            'company_name' => $lead->company_name,
            'owner_name' => $lead->contact_person,
            'email' => $lead->email,
            'phone' => $lead->phone,
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

    $quotation = Quotation::query()->where('platform_lead_id', $lead->id)->first();

    expect($quotation)->not->toBeNull()
        ->and($quotation->status)->toBe(QuotationStatus::Draft)
        ->and($lead->refresh()->stage)->toBe(PlatformLeadStage::QuotationSent);

    $this->actingAs($admin)
        ->get(route('platform.quotations.show', $quotation))
        ->assertOk()
        ->assertSee('Related Lead')
        ->assertSee('Modal Realty');
});
