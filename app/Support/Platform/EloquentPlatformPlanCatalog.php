<?php

namespace App\Support\Platform;

use App\Contracts\PlatformPlanCatalog;
use App\Enums\PlanStatus;
use App\Models\Plan;

class EloquentPlatformPlanCatalog implements PlatformPlanCatalog
{
    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     price_monthly: int|null,
     *     price_monthly_label: string,
     *     price_annual: int|null,
     *     price_annual_label: string,
     *     currency: string,
     *     trial_days: int|null,
     *     limits: array<string, int|null>
     * }>
     */
    public function options(): array
    {
        return Plan::query()
            ->active()
            ->orderBy('price_monthly')
            ->orderBy('id')
            ->get()
            ->map(fn (Plan $plan): array => $this->toOption($plan))
            ->all();
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     price_monthly: int|null,
     *     price_monthly_label: string,
     *     price_annual: int|null,
     *     price_annual_label: string,
     *     currency: string,
     *     trial_days: int|null,
     *     limits: array<string, int|null>
     * }|null
     */
    public function find(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        $plan = Plan::query()->where('key', $key)->first();

        if ($plan === null) {
            return null;
        }

        return $this->toOption($plan);
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     price_monthly: int|null,
     *     price_monthly_label: string,
     *     price_annual: int|null,
     *     price_annual_label: string,
     *     currency: string,
     *     trial_days: int|null,
     *     limits: array<string, int|null>
     * }
     */
    private function toOption(Plan $plan): array
    {
        return [
            'key' => $plan->key,
            'label' => $plan->name,
            'price_monthly' => $plan->price_monthly,
            'price_monthly_label' => $plan->monthlyPriceLabel(),
            'price_annual' => $plan->price_annual,
            'price_annual_label' => $plan->annualPriceLabel(),
            'currency' => $plan->currency,
            'trial_days' => $plan->trial_enabled ? $plan->trial_days : null,
            'limits' => $plan->limits ?? [],
            'status' => $plan->status instanceof PlanStatus ? $plan->status->value : (string) $plan->status,
        ];
    }
}
