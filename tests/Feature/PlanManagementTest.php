<?php

use App\Contracts\PlatformPlanCatalog;
use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\PlanPackPreset;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Platform\PlanDefinitionCatalog;

test('super admins can view the plans list', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.plans'))
        ->assertOk()
        ->assertSee('Plans')
        ->assertSee('Manage the plans and pricing available to Channel Partners.')
        ->assertSee('Create Plan')
        ->assertSee('Starter')
        ->assertSee('Growth')
        ->assertSee('Pro')
        ->assertDontSee('Coming soon');
});

test('non super admins cannot view plans', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('platform.plans'))
        ->assertForbidden();
});

test('super admins can create a plan through the wizard', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('platform.plans.wizard.basic.store'), [
            'name' => 'Elite',
            'description' => 'Top tier packaging',
            'status' => PlanStatus::Active->value,
        ])
        ->assertRedirect(route('platform.plans.wizard.pricing'));

    $this->actingAs($admin)
        ->post(route('platform.plans.wizard.pricing.store'), [
            'price_monthly' => 9999,
            'price_annual' => 99990,
            'trial_enabled' => '1',
            'trial_days' => 14,
        ])
        ->assertRedirect(route('platform.plans.wizard.features'));

    $this->actingAs($admin)
        ->post(route('platform.plans.wizard.features.store'), [
            'features' => [
                PlanFeature::Crm->value => '1',
                PlanFeature::InsyteAi->value => '1',
            ],
            'packs' => [
                PlanFeature::Crm->value => PlanPack::Advanced->value,
                PlanFeature::InsyteAi->value => PlanPack::Basic->value,
            ],
            'capabilities' => [PlanCapability::IntegrationApi->value],
        ])
        ->assertRedirect(route('platform.plans.wizard.limits'));

    $this->actingAs($admin)
        ->post(route('platform.plans.wizard.limits.store'), [
            'limits' => [
                'users' => 8,
                'leads' => 3000,
            ],
        ])
        ->assertRedirect(route('platform.plans.wizard.review'));

    $this->actingAs($admin)
        ->post(route('platform.plans.store'))
        ->assertRedirect();

    $plan = Plan::query()->where('name', 'Elite')->first();

    expect($plan)->not->toBeNull()
        ->and($plan->price_monthly)->toBe(9999)
        ->and($plan->trial_days)->toBe(14)
        ->and($plan->hasFeature(PlanFeature::Crm))->toBeTrue()
        ->and($plan->hasFeature(PlanFeature::InsyteAi))->toBeTrue()
        ->and($plan->limitFor('users'))->toBe(8);
});

test('super admins can open plan tabs', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('platform.plans.show', $plan))
        ->assertOk()
        ->assertSee('Growth')
        ->assertSee('Overview')
        ->assertSee('Features')
        ->assertSee('Limits')
        ->assertSee('Partners')
        ->assertSee('Included Features');

    $this->actingAs($admin)
        ->get(route('platform.plans.features', $plan))
        ->assertOk()
        ->assertSee('Edit Features')
        ->assertSee('What Basic and Advanced mean');

    $this->actingAs($admin)
        ->get(route('platform.plans.limits', $plan))
        ->assertOk()
        ->assertSee('Edit Limits')
        ->assertDontSee('Storage');

    $this->actingAs($admin)
        ->get(route('platform.plans.partners', $plan))
        ->assertOk()
        ->assertSee('View All Partners');
});

test('plan partner counts use virtual tenant plan_key attributes', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();

    Tenant::factory()->create([
        'id' => 'growthco',
        'name' => 'Growth Co Partners',
        'plan_key' => 'growth',
    ]);

    Tenant::factory()->create([
        'id' => 'starterco',
        'name' => 'Starter Co Partners',
        'plan_key' => 'starter',
    ]);

    expect($plan->activePartnersCount())->toBe(1);

    $this->actingAs($admin)
        ->get(route('platform.plans.show', $plan))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('platform.plans.partners', $plan))
        ->assertOk()
        ->assertSee('Growth Co Partners')
        ->assertDontSee('Starter Co Partners');
});

test('super admins can update a plan', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('platform.plans.update', $plan), [
            'name' => 'Growth Plus',
            'description' => $plan->description,
            'status' => PlanStatus::Active->value,
            'price_monthly' => 5999,
            'price_annual' => 59990,
            'trial_enabled' => '1',
            'trial_days' => 7,
            'features' => $plan->features,
            'packs' => $plan->packs,
            'capabilities' => $plan->capabilities,
            'limits' => $plan->limits,
        ])
        ->assertRedirect(route('platform.plans.show', $plan));

    expect($plan->fresh()->name)->toBe('Growth Plus')
        ->and($plan->fresh()->price_monthly)->toBe(5999);
});

test('super admins can duplicate a plan and then archive it', function () {
    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'starter')->firstOrFail();

    $this->actingAs($admin)
        ->from(route('platform.plans.duplicate', $plan))
        ->post(route('platform.plans.duplicate.store', $plan), [
            'name' => 'Starter Plus',
            'copy_pricing' => '1',
            'copy_features' => '1',
            'copy_limits' => '1',
            'copy_trial' => '1',
        ])
        ->assertRedirect();

    $copy = Plan::query()->where('name', 'Starter Plus')->first();

    expect($copy)->not->toBeNull()
        ->and($copy->price_monthly)->toBe($plan->price_monthly)
        ->and($copy->status)->toBe(PlanStatus::Active);

    $this->actingAs($admin)
        ->get(route('platform.plans.edit', $copy))
        ->assertOk()
        ->assertSee('Edit Plan');

    $this->actingAs($admin)
        ->post(route('platform.plans.archive', $copy))
        ->assertRedirect(route('platform.plans'));

    expect($copy->fresh()->status)->toBe(PlanStatus::Archived);
});

test('archived plans are hidden from the channel partner catalog', function () {
    $plan = Plan::query()->where('key', 'starter')->firstOrFail();
    $plan->update(['status' => PlanStatus::Archived]);

    $options = app(PlatformPlanCatalog::class)->options();

    expect(collect($options)->pluck('key')->all())
        ->not->toContain('starter')
        ->toContain('growth');

    expect(app(PlatformPlanCatalog::class)->find('starter'))
        ->not->toBeNull()
        ->and(app(PlatformPlanCatalog::class)->find('starter')['label'])->toBe('Starter');
});

test('super admins can redefine basic and advanced packs', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->from(route('platform.plans.features', 'growth'))
        ->post(route('platform.plans.presets.update'), [
            'presets' => [
                PlanFeature::InsyteAi->value => [
                    'basic' => [PlanCapability::AiChat->value],
                    'advanced' => PlanDefinitionCatalog::defaultPresetKeys(PlanFeature::InsyteAi, PlanPack::Advanced),
                ],
            ],
        ])
        ->assertRedirect();

    $keys = PlanPackPreset::capabilityKeys(PlanFeature::InsyteAi, PlanPack::Basic);

    expect($keys)->toBe([PlanCapability::AiChat->value]);
});
