<?php

namespace App\Actions;

use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use App\Models\Plan;

class CreatePlan
{
    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     status?: string,
     *     price_monthly: int,
     *     price_annual: int,
     *     trial_enabled?: bool,
     *     trial_days?: int,
     *     features?: array<string, bool>,
     *     packs?: array<string, string>,
     *     capabilities?: list<string>,
     *     limits?: array<string, int|null>
     * }  $data
     */
    public function handle(array $data, ?string $key = null): Plan
    {
        return Plan::query()->create($this->attributes($data, $key ?: Plan::uniqueKeyFromName($data['name'])));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function attributes(array $data, string $key): array
    {
        return [
            'key' => $key,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? PlanStatus::Active->value,
            'price_monthly' => $data['price_monthly'],
            'price_annual' => $data['price_annual'],
            'currency' => 'INR',
            'trial_enabled' => (bool) ($data['trial_enabled'] ?? false),
            'trial_days' => (int) ($data['trial_days'] ?? 7),
            'features' => $this->features($data['features'] ?? []),
            'packs' => $this->packs($data['features'] ?? [], $data['packs'] ?? []),
            'capabilities' => array_values($data['capabilities'] ?? []),
            'limits' => $this->limits($data['limits'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $features
     * @return array<string, bool>
     */
    private function features(array $features): array
    {
        $normalized = [];

        foreach (PlanFeature::cases() as $feature) {
            $normalized[$feature->value] = (bool) ($features[$feature->value] ?? false);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $features
     * @param  array<string, mixed>  $packs
     * @return array<string, string>
     */
    private function packs(array $features, array $packs): array
    {
        $normalized = [];

        foreach (PlanFeature::cases() as $feature) {
            $enabled = (bool) ($features[$feature->value] ?? false);

            if (! $enabled) {
                $normalized[$feature->value] = PlanPack::Off->value;

                continue;
            }

            $pack = PlanPack::tryFrom((string) ($packs[$feature->value] ?? ''));

            if ($feature->isPackable()) {
                $normalized[$feature->value] = ($pack ?? PlanPack::Basic)->value;
            } else {
                $normalized[$feature->value] = PlanPack::Advanced->value;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $limits
     * @return array<string, int|null>
     */
    private function limits(array $limits): array
    {
        $normalized = [];

        foreach (PlanLimitKey::cases() as $limit) {
            $value = $limits[$limit->value] ?? null;
            $normalized[$limit->value] = $value === null || $value === '' ? null : (int) $value;
        }

        return $normalized;
    }
}
