<?php

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanPack;
use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Mail\UtilityTemplatedMail;
use App\Models\Plan;
use App\Models\UtilityEmailTemplate;
use App\Models\UtilityMailSetting;
use App\Support\Platform\PlanDefinitionCatalog;
use Illuminate\Support\Facades\Mail;

test('integrations tab shows utilities card when plan allows it', function () {
    createTestTenant(['plan_key' => 'growth']);
    actingAsTenantUser();

    $this->get('/acme/settings?tab=integrations')
        ->assertOk()
        ->assertSee('Utilities')
        ->assertSee('Workspace tools');
});

test('utilities settings page seeds templates and saves smtp', function () {
    createTestTenant(['plan_key' => 'growth']);
    actingAsTenantUser();

    $this->get('/acme/settings/integrations/utilities')
        ->assertOk()
        ->assertSee('SMTP Server')
        ->assertSee('Sender Identity')
        ->assertSee('Team user welcome');

    expect(UtilityEmailTemplate::query()->count())->toBe(2);

    $this->put('/acme/settings/integrations/utilities/mail', [
        'host' => 'smtp.tenant.test',
        'port' => 587,
        'username' => 'crm@acme.test',
        'password' => 'tenant-secret',
        'from_email' => 'crm@acme.test',
        'from_name' => 'Acme CRM',
        'reply_to_email' => 'support@acme.test',
        'reply_to_name' => 'Acme Support',
        'notes' => 'Primary SMTP',
        'is_active' => '1',
        'encryption' => UtilityMailEncryption::Tls->value,
        'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Always->value,
    ])
        ->assertRedirect('/acme/settings/integrations/utilities')
        ->assertSessionHas('status');

    expect(UtilityMailSetting::current()?->host)->toBe('smtp.tenant.test')
        ->and(UtilityMailSetting::current()?->reply_to_email)->toBe('support@acme.test')
        ->and(UtilityMailSetting::current()?->is_active)->toBeTrue()
        ->and(UtilityMailSetting::current()?->isConfigured())->toBeTrue();
});

test('utilities page is blocked without plan capability', function () {
    $plan = Plan::factory()->create([
        'key' => 'no-utilities',
        'name' => 'No Utilities',
        'features' => PlanDefinitionCatalog::defaultFeatures([
            PlanFeature::Crm,
            PlanFeature::Integrations,
        ]),
        'packs' => PlanDefinitionCatalog::defaultPacks([
            PlanFeature::Crm->value => PlanPack::Basic,
        ]),
        'capabilities' => [PlanCapability::IntegrationApi->value],
    ]);

    createTestTenant(['plan_key' => $plan->key]);
    actingAsTenantUser();

    $this->get('/acme/settings/integrations/utilities')->assertForbidden();
});

test('tenant test email sends through utility smtp', function () {
    Mail::fake();

    createTestTenant(['plan_key' => 'growth']);
    actingAsTenantUser();

    UtilityMailSetting::query()->create([
        'host' => 'smtp.tenant.test',
        'port' => 587,
        'username' => 'crm@acme.test',
        'password' => 'tenant-secret',
        'from_email' => 'crm@acme.test',
        'from_name' => 'Acme CRM',
        'encryption' => UtilityMailEncryption::Tls,
        'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Ask,
    ]);

    $this->post('/acme/settings/integrations/utilities/test', [
        'test_email' => 'admin@acme.test',
    ])
        ->assertRedirect('/acme/settings/integrations/utilities')
        ->assertSessionHas('status', __('Test email sent.'));

    Mail::assertSent(UtilityTemplatedMail::class);
});
