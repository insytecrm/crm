<?php

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $capability = PlanCapability::UtilitiesEmail->value;

        Plan::query()->each(function (Plan $plan) use ($capability): void {
            if ($plan->key === 'starter') {
                return;
            }

            if (! $plan->hasFeature(PlanFeature::Integrations)) {
                return;
            }

            $capabilities = is_array($plan->capabilities) ? $plan->capabilities : [];

            if (in_array($capability, $capabilities, true)) {
                return;
            }

            $plan->update([
                'capabilities' => array_values(array_unique([
                    ...$capabilities,
                    $capability,
                ])),
            ]);
        });
    }

    public function down(): void
    {
        $capability = PlanCapability::UtilitiesEmail->value;

        Plan::query()->each(function (Plan $plan) use ($capability): void {
            $capabilities = is_array($plan->capabilities) ? $plan->capabilities : [];

            if (! in_array($capability, $capabilities, true)) {
                return;
            }

            $plan->update([
                'capabilities' => array_values(array_filter(
                    $capabilities,
                    fn (mixed $value): bool => $value !== $capability,
                )),
            ]);
        });
    }
};
