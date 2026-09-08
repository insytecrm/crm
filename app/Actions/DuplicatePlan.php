<?php

namespace App\Actions;

use App\Enums\PlanStatus;
use App\Models\Plan;

class DuplicatePlan
{
    public function __construct(private CreatePlan $createPlan) {}

    /**
     * @param  array{name: string, copy_pricing?: bool, copy_features?: bool, copy_limits?: bool, copy_trial?: bool}  $data
     */
    public function handle(Plan $plan, array $data): Plan
    {
        $copyPricing = (bool) ($data['copy_pricing'] ?? true);
        $copyFeatures = (bool) ($data['copy_features'] ?? true);
        $copyLimits = (bool) ($data['copy_limits'] ?? true);
        $copyTrial = (bool) ($data['copy_trial'] ?? true);

        return $this->createPlan->handle([
            'name' => $data['name'],
            'description' => $plan->description,
            'status' => PlanStatus::Active->value,
            'price_monthly' => $copyPricing ? $plan->price_monthly : 0,
            'price_annual' => $copyPricing ? $plan->price_annual : 0,
            'trial_enabled' => $copyTrial ? $plan->trial_enabled : false,
            'trial_days' => $copyTrial ? $plan->trial_days : 7,
            'features' => $copyFeatures ? ($plan->features ?? []) : [],
            'packs' => $copyFeatures ? ($plan->packs ?? []) : [],
            'capabilities' => $copyFeatures ? ($plan->capabilities ?? []) : [],
            'limits' => $copyLimits ? ($plan->limits ?? []) : [],
        ]);
    }
}
