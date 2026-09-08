<?php

namespace App\Support\Platform;

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use Illuminate\Validation\Rule;

class PlanFormRules
{
    /**
     * @return array<string, mixed>
     */
    public static function basic(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(PlanStatus::class)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pricing(): array
    {
        return [
            'price_monthly' => ['required', 'integer', 'min:0'],
            'price_annual' => ['required', 'integer', 'min:0'],
            'trial_enabled' => ['sometimes', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function features(): array
    {
        $featureKeys = array_map(fn (PlanFeature $feature): string => $feature->value, PlanFeature::cases());
        $capabilityKeys = array_map(fn (PlanCapability $capability): string => $capability->value, PlanCapability::cases());
        $packKeys = array_map(fn (PlanPack $pack): string => $pack->value, PlanPack::cases());

        return [
            'features' => ['required', 'array'],
            'features.*' => ['boolean'],
            ...collect($featureKeys)->mapWithKeys(fn (string $key): array => [
                'features.'.$key => ['sometimes', 'boolean'],
            ])->all(),
            'packs' => ['nullable', 'array'],
            'packs.*' => ['nullable', Rule::in($packKeys)],
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['string', Rule::in($capabilityKeys)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function limits(): array
    {
        $rules = [
            'limits' => ['required', 'array'],
        ];

        foreach (PlanLimitKey::cases() as $limit) {
            $rules['limits.'.$limit->value] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public static function presets(): array
    {
        $capabilityKeys = array_map(fn (PlanCapability $capability): string => $capability->value, PlanCapability::cases());

        return [
            'presets' => ['required', 'array'],
            'presets.*' => ['array'],
            'presets.*.basic' => ['nullable', 'array'],
            'presets.*.advanced' => ['nullable', 'array'],
            'presets.*.basic.*' => ['string', Rule::in($capabilityKeys)],
            'presets.*.advanced.*' => ['string', Rule::in($capabilityKeys)],
        ];
    }
}
