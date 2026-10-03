<?php

namespace App\Support\Platform;

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Models\Plan;

class PlanEntitlement
{
    /**
     * @param  list<string>  $features
     * @param  list<string>  $capabilities
     * @param  array<string, int|null>  $limits
     */
    public function __construct(
        public readonly bool $unrestricted,
        public readonly array $features,
        public readonly array $capabilities,
        public readonly array $limits,
        public readonly ?Plan $plan = null,
    ) {}

    public static function unrestricted(): self
    {
        return new self(
            unrestricted: true,
            features: array_map(fn (PlanFeature $feature): string => $feature->value, PlanFeature::cases()),
            capabilities: array_map(fn (PlanCapability $capability): string => $capability->value, PlanCapability::cases()),
            limits: array_fill_keys(
                array_map(fn (PlanLimitKey $limit): string => $limit->value, PlanLimitKey::cases()),
                null,
            ),
        );
    }

    public static function fromPlan(Plan $plan): self
    {
        $features = [];

        foreach (PlanFeature::cases() as $feature) {
            if ($plan->hasFeature($feature)) {
                $features[] = $feature->value;
            }
        }

        $limits = [];

        foreach (PlanLimitKey::cases() as $limit) {
            $limits[$limit->value] = $plan->limitFor($limit->value);
        }

        return new self(
            unrestricted: false,
            features: $features,
            capabilities: $plan->resolvedCapabilityKeys(),
            limits: $limits,
            plan: $plan,
        );
    }

    public function hasFeature(PlanFeature|string $feature): bool
    {
        if ($this->unrestricted) {
            return true;
        }

        $key = $feature instanceof PlanFeature ? $feature->value : $feature;

        return in_array($key, $this->features, true);
    }

    public function hasCapability(PlanCapability|string $capability): bool
    {
        if ($this->unrestricted) {
            return true;
        }

        $key = $capability instanceof PlanCapability ? $capability->value : $capability;

        return in_array($key, $this->capabilities, true);
    }

    /**
     * @return int|null Null means unlimited.
     */
    public function limit(PlanLimitKey|string $key): ?int
    {
        if ($this->unrestricted) {
            return null;
        }

        $limitKey = $key instanceof PlanLimitKey ? $key->value : $key;

        return $this->limits[$limitKey] ?? null;
    }
}
