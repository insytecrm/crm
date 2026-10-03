<?php

namespace App\Actions;

use App\Enums\PlanCapability;
use App\Enums\PlanPack;
use App\Models\PlanPackPreset;
use App\Support\Platform\PlanDefinitionCatalog;

class UpdatePlanPackPresets
{
    /**
     * @param  array<string, array<string, list<string>>>  $presets
     */
    public function handle(array $presets): void
    {
        foreach (PlanDefinitionCatalog::packableFeatures() as $feature) {
            foreach ([PlanPack::Basic, PlanPack::Advanced] as $pack) {
                $keys = $presets[$feature->value][$pack->value] ?? [];
                $allowed = array_map(
                    fn (PlanCapability $capability): string => $capability->value,
                    PlanCapability::forFeature($feature),
                );

                PlanPackPreset::query()->updateOrCreate(
                    [
                        'module' => $feature->value,
                        'pack' => $pack->value,
                    ],
                    [
                        'capabilities' => array_values(array_intersect($keys, $allowed)),
                    ],
                );
            }
        }
    }
}
