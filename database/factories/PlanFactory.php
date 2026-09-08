<?php

namespace Database\Factories;

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Support\Platform\PlanDefinitionCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        $packs = [];

        foreach (PlanFeature::cases() as $feature) {
            $packs[$feature->value] = PlanPack::Advanced;
        }

        return [
            'key' => Str::slug($name).fake()->unique()->numerify('###'),
            'name' => Str::title($name),
            'description' => fake()->sentence(),
            'status' => PlanStatus::Active,
            'price_monthly' => 4999,
            'price_annual' => 49990,
            'currency' => 'INR',
            'trial_enabled' => true,
            'trial_days' => 7,
            'features' => PlanDefinitionCatalog::defaultFeatures(PlanFeature::cases()),
            'packs' => PlanDefinitionCatalog::defaultPacks($packs),
            'capabilities' => array_map(
                fn (PlanCapability $capability): string => $capability->value,
                PlanCapability::forFeature(PlanFeature::Integrations),
            ),
            'limits' => PlanDefinitionCatalog::defaultLimits([
                PlanLimitKey::Users->value => 15,
                PlanLimitKey::Leads->value => 10000,
                PlanLimitKey::Automations->value => 50,
            ]),
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => PlanStatus::Archived,
        ]);
    }
}
