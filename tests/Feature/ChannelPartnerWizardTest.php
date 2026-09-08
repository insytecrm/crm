<?php

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('create quotation opens as a modal on the partners list', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('tenants.create'))
        ->assertRedirect(route('tenants.index', ['quote' => 1]));

    $this->actingAs($admin)
        ->get(route('tenants.index', ['quote' => 1]))
        ->assertOk()
        ->assertSee('Create Quotation')
        ->assertSee('create-quotation')
        ->assertSee('open-modal')
        ->assertSee('Company Name')
        ->assertSee('quotationCreateWizard')
        ->assertDontSee('>Add Channel Partner</');
});

test('super admins can create a channel partner through the wizard modal', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->from(route('tenants.index'))
        ->post(route('tenants.wizard.store'), [
            'name' => 'ABC Realty',
            'owner_name' => 'Rahul Sharma',
            'email' => 'hello@abcrealty.test',
            'phone' => '9999999999',
            'location' => 'Pune',
            'slug' => 'abcrealty',
            'plan_key' => 'growth',
            'billing_cycle' => 'monthly',
            'start_trial' => '1',
            'trial_days' => 7,
            'admin_name' => 'Rahul Sharma',
            'admin_email' => 'rahul@abcrealty.test',
            'send_invitation' => '1',
            '_wizard' => '1',
        ])
        ->assertRedirect(route('tenants.wizard.success', 'abcrealty'));

    $tenant = Tenant::query()->find('abcrealty');

    expect($tenant)->not->toBeNull()
        ->and($tenant->name)->toBe('ABC Realty')
        ->and($tenant->status)->toBe(TenantStatus::Active)
        ->and($tenant->owner_name)->toBe('Rahul Sharma')
        ->and($tenant->plan_key)->toBe('growth')
        ->and($tenant->billing_cycle)->toBe('monthly')
        ->and((int) $tenant->trial_days)->toBe(7);

    $tenant->run(function () {
        expect(DB::table('users')->where('email', 'rahul@abcrealty.test')->exists())->toBeTrue();
    });

    $this->actingAs($admin)
        ->get(route('tenants.wizard.success', $tenant))
        ->assertOk()
        ->assertSee('Channel Partner Created')
        ->assertSee('ABC Realty is ready to use InSyte.')
        ->assertSee('Go to Partner');
});

test('wizard validation errors reopen the add modal on the partners list', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->from(route('tenants.index'))
        ->post(route('tenants.wizard.store'), [
            'name' => '',
            '_wizard' => '1',
            '_wizard_step' => '1',
        ])
        ->assertRedirect(route('tenants.index', ['add' => 1]))
        ->assertSessionHasErrors(['name', 'owner_name', 'email', 'plan_key', 'billing_cycle', 'admin_name', 'admin_email']);

    $this->actingAs($admin)
        ->get(route('tenants.index', ['add' => 1]))
        ->assertOk()
        ->assertSee('add-channel-partner')
        ->assertSee('Company Name');
});
