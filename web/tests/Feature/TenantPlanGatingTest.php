<?php

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Models\Plan;
use App\Support\Platform\PlanDefinitionCatalog;
use App\Support\Platform\TenantPlanAccess;

test('tenants without a plan keep the full crm', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/ai')->assertOk()->assertSee('InSyte AI OS');
    $this->get('/acme/automations/workflows')->assertOk();
    $this->get('/acme/reports/analytics')->assertOk();
    $this->get('/acme/leads/duplicates')->assertOk();
});

test('tenants on a restricted plan cannot open modules that are turned off', function () {
    $plan = Plan::factory()->create([
        'key' => 'lite',
        'name' => 'Lite',
        'features' => PlanDefinitionCatalog::defaultFeatures([
            PlanFeature::Crm,
            PlanFeature::Reports,
        ]),
        'packs' => PlanDefinitionCatalog::defaultPacks([
            PlanFeature::Crm->value => PlanPack::Basic,
            PlanFeature::Reports->value => PlanPack::Basic,
        ]),
        'capabilities' => [],
        'limits' => PlanDefinitionCatalog::defaultLimits([
            PlanLimitKey::Users->value => 2,
            PlanLimitKey::Leads->value => 1,
        ]),
    ]);

    createTestTenant([
        'plan_key' => $plan->key,
    ]);
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertDontSee('InSyte AI OS')
        ->assertSee('Leads');

    $this->get('/acme/ai')->assertForbidden();
    $this->get('/acme/automations/workflows')->assertForbidden();
    $this->get('/acme/leads/duplicates')->assertForbidden();
    $this->get('/acme/reports/analytics')->assertForbidden();
    $this->get('/acme/reports')->assertOk();
});

test('tenants cannot create more leads than the plan allows', function () {
    $plan = Plan::factory()->create([
        'key' => 'capped',
        'name' => 'Capped',
        'features' => PlanDefinitionCatalog::defaultFeatures([PlanFeature::Crm]),
        'packs' => PlanDefinitionCatalog::defaultPacks([
            PlanFeature::Crm->value => PlanPack::Basic,
        ]),
        'limits' => PlanDefinitionCatalog::defaultLimits([
            PlanLimitKey::Leads->value => 1,
        ]),
    ]);

    createTestTenant(['plan_key' => $plan->key]);
    actingAsTenantUser();

    $this->post('/acme/leads', [
        'name' => 'First Lead',
        'phone' => '9999999999',
    ])->assertRedirect();

    $this->from('/acme/leads')
        ->post('/acme/leads', [
            'name' => 'Second Lead',
            'phone' => '8888888888',
        ])
        ->assertRedirect('/acme/leads')
        ->assertSessionHasErrors('plan');
});

test('basic ai plans cannot use advanced ai tools', function () {
    $plan = Plan::factory()->create([
        'key' => 'ai-basic',
        'name' => 'AI Basic',
        'features' => PlanDefinitionCatalog::defaultFeatures([
            PlanFeature::Crm,
            PlanFeature::InsyteAi,
        ]),
        'packs' => PlanDefinitionCatalog::defaultPacks([
            PlanFeature::Crm->value => PlanPack::Basic,
            PlanFeature::InsyteAi->value => PlanPack::Basic,
        ]),
        'limits' => PlanDefinitionCatalog::defaultLimits([
            PlanLimitKey::AiMessagesMonthly->value => 50,
        ]),
    ]);

    createTestTenant(['plan_key' => $plan->key]);
    actingAsTenantUser();

    $this->get('/acme/ai')
        ->assertOk()
        ->assertSee('Chat');

    $access = app(TenantPlanAccess::class);

    expect($access->hasCapability(PlanCapability::AiChat))->toBeTrue()
        ->and($access->hasCapability(PlanCapability::AiScheduleFollowUp))->toBeFalse();
});
