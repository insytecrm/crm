<?php

namespace App\Actions;

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\PlanPackPreset;
use App\Support\Platform\PlanDefinitionCatalog;

class SeedDefaultPlans
{
    public function __construct(private CreatePlan $createPlan) {}

    public function handle(): void
    {
        $this->seedPresets();
        $this->seedPlans();
    }

    private function seedPresets(): void
    {
        foreach (PlanDefinitionCatalog::packableFeatures() as $feature) {
            foreach ([PlanPack::Basic, PlanPack::Advanced] as $pack) {
                PlanPackPreset::query()->updateOrCreate(
                    [
                        'module' => $feature->value,
                        'pack' => $pack->value,
                    ],
                    [
                        'capabilities' => PlanDefinitionCatalog::defaultPresetKeys($feature, $pack),
                    ],
                );
            }
        }
    }

    private function seedPlans(): void
    {
        foreach ($this->plans() as $attributes) {
            if (Plan::query()->where('key', $attributes['key'])->exists()) {
                continue;
            }

            $this->createPlan->handle($attributes, $attributes['key']);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function plans(): array
    {
        return [
            [
                'key' => 'starter',
                'name' => __('Starter'),
                'description' => __('Essential CRM for smaller channel partner teams.'),
                'status' => PlanStatus::Active->value,
                'price_monthly' => 2999,
                'price_annual' => 29990,
                'trial_enabled' => true,
                'trial_days' => 7,
                'features' => $this->features([
                    PlanFeature::Crm,
                    PlanFeature::Properties,
                    PlanFeature::Bookings,
                    PlanFeature::Revenue,
                    PlanFeature::Reports,
                    PlanFeature::Teams,
                    PlanFeature::Automations,
                    PlanFeature::TeamInbox,
                    PlanFeature::WhatsApp,
                    PlanFeature::Integrations,
                ]),
                'packs' => $this->packs([
                    PlanFeature::Crm->value => PlanPack::Basic,
                    PlanFeature::Reports->value => PlanPack::Basic,
                    PlanFeature::Automations->value => PlanPack::Basic,
                ]),
                'capabilities' => [
                    PlanCapability::IntegrationApi->value,
                    PlanCapability::IntegrationGoogleSheets->value,
                ],
                'limits' => $this->limitValues([
                    PlanLimitKey::Users->value => 5,
                    PlanLimitKey::Leads->value => 2000,
                    PlanLimitKey::Automations->value => 10,
                    PlanLimitKey::AutomationRunsMonthly->value => 500,
                    PlanLimitKey::WhatsAppMessagesMonthly->value => 500,
                    PlanLimitKey::AiMessagesMonthly->value => 0,
                    PlanLimitKey::Integrations->value => 2,
                    PlanLimitKey::Properties->value => 20,
                    PlanLimitKey::Teams->value => 2,
                    PlanLimitKey::Microsites->value => 0,
                ]),
            ],
            [
                'key' => 'growth',
                'name' => __('Growth'),
                'description' => __('Full CRM with automations, reports, and basic AI.'),
                'status' => PlanStatus::Active->value,
                'price_monthly' => 4999,
                'price_annual' => 49990,
                'trial_enabled' => true,
                'trial_days' => 7,
                'features' => $this->features([
                    PlanFeature::Crm,
                    PlanFeature::Properties,
                    PlanFeature::Bookings,
                    PlanFeature::Revenue,
                    PlanFeature::Reports,
                    PlanFeature::Teams,
                    PlanFeature::Automations,
                    PlanFeature::TeamInbox,
                    PlanFeature::InsyteAi,
                    PlanFeature::WhatsApp,
                    PlanFeature::Microsites,
                    PlanFeature::CustomDomains,
                    PlanFeature::Integrations,
                ]),
                'packs' => $this->packs([
                    PlanFeature::Crm->value => PlanPack::Advanced,
                    PlanFeature::Reports->value => PlanPack::Advanced,
                    PlanFeature::Automations->value => PlanPack::Advanced,
                    PlanFeature::InsyteAi->value => PlanPack::Basic,
                ]),
                'capabilities' => [
                    PlanCapability::IntegrationApi->value,
                    PlanCapability::IntegrationGoogleSheets->value,
                    PlanCapability::IntegrationFacebook->value,
                ],
                'limits' => $this->limitValues([
                    PlanLimitKey::Users->value => 15,
                    PlanLimitKey::Leads->value => 10000,
                    PlanLimitKey::Automations->value => 50,
                    PlanLimitKey::AutomationRunsMonthly->value => 5000,
                    PlanLimitKey::WhatsAppMessagesMonthly->value => 5000,
                    PlanLimitKey::AiMessagesMonthly->value => 1000,
                    PlanLimitKey::Integrations->value => 5,
                    PlanLimitKey::Properties->value => 100,
                    PlanLimitKey::Teams->value => 5,
                    PlanLimitKey::Microsites->value => 20,
                ]),
            ],
            [
                'key' => 'pro',
                'name' => __('Pro'),
                'description' => __('Advanced AI and packaging for high-volume teams.'),
                'status' => PlanStatus::Active->value,
                'price_monthly' => 7999,
                'price_annual' => 79990,
                'trial_enabled' => true,
                'trial_days' => 7,
                'features' => $this->features(PlanFeature::cases()),
                'packs' => $this->packs([
                    PlanFeature::Crm->value => PlanPack::Advanced,
                    PlanFeature::Reports->value => PlanPack::Advanced,
                    PlanFeature::Automations->value => PlanPack::Advanced,
                    PlanFeature::InsyteAi->value => PlanPack::Advanced,
                ]),
                'capabilities' => array_map(
                    fn (PlanCapability $capability): string => $capability->value,
                    PlanCapability::forFeature(PlanFeature::Integrations),
                ),
                'limits' => $this->limitValues([
                    PlanLimitKey::Users->value => 50,
                    PlanLimitKey::Leads->value => 50000,
                    PlanLimitKey::Automations->value => 200,
                    PlanLimitKey::AutomationRunsMonthly->value => 20000,
                    PlanLimitKey::WhatsAppMessagesMonthly->value => 20000,
                    PlanLimitKey::AiMessagesMonthly->value => 10000,
                    PlanLimitKey::Integrations->value => 15,
                    PlanLimitKey::Properties->value => null,
                    PlanLimitKey::Teams->value => null,
                    PlanLimitKey::Microsites->value => null,
                ]),
            ],
        ];
    }

    /**
     * @param  list<PlanFeature>  $enabled
     * @return array<string, bool>
     */
    private function features(array $enabled): array
    {
        return PlanDefinitionCatalog::defaultFeatures($enabled);
    }

    /**
     * @param  array<string, PlanPack>  $packs
     * @return array<string, string>
     */
    private function packs(array $packs): array
    {
        $normalized = [];

        foreach ($packs as $feature => $pack) {
            $key = $feature instanceof PlanFeature ? $feature->value : (string) $feature;
            $normalized[$key] = $pack;
        }

        return PlanDefinitionCatalog::defaultPacks($normalized);
    }

    /**
     * @param  array<string, int|null>  $limits
     * @return array<string, int|null>
     */
    private function limitValues(array $limits): array
    {
        $normalized = [];

        foreach ($limits as $limit => $value) {
            $key = $limit instanceof PlanLimitKey ? $limit->value : (string) $limit;
            $normalized[$key] = $value;
        }

        return PlanDefinitionCatalog::defaultLimits($normalized);
    }
}
