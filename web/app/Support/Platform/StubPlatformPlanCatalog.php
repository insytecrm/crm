<?php

namespace App\Support\Platform;

use App\Contracts\PlatformPlanCatalog;

/**
 * Temporary catalog until the Plans module owns plan definitions.
 * Keep option keys and array shapes stable so Channel Partner screens do not change.
 */
class StubPlatformPlanCatalog implements PlatformPlanCatalog
{
    public function options(): array
    {
        return [
            $this->plan('starter', __('Starter'), 1999, [
                'users' => 5,
                'leads' => 2000,
                'automations' => 10,
                'storage_gb' => 2.0,
            ]),
            $this->plan('growth', __('Growth'), 4999, [
                'users' => 15,
                'leads' => 10000,
                'automations' => 50,
                'storage_gb' => 10.0,
            ]),
            $this->plan('pro', __('Pro'), 9999, [
                'users' => 50,
                'leads' => 50000,
                'automations' => 200,
                'storage_gb' => 50.0,
            ]),
        ];
    }

    public function find(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        foreach ($this->options() as $option) {
            if ($option['key'] === $key) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @param  array{users: int|null, leads: int|null, automations: int|null, storage_gb: float|null}  $limits
     * @return array{
     *     key: string,
     *     label: string,
     *     price_monthly: int|null,
     *     price_monthly_label: string,
     *     currency: string,
     *     limits: array{users: int|null, leads: int|null, automations: int|null, storage_gb: float|null}
     * }
     */
    private function plan(string $key, string $label, int $priceMonthly, array $limits): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'price_monthly' => $priceMonthly,
            'price_monthly_label' => '₹'.number_format($priceMonthly),
            'currency' => 'INR',
            'limits' => $limits,
        ];
    }
}
