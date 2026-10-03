<?php

namespace App\Contracts;

interface PlatformPlanCatalog
{
    /**
     * Stable plan catalog shape for Channel Partner screens and onboarding.
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     price_monthly: int|null,
     *     price_monthly_label: string,
     *     currency: string,
     *     limits: array<string, int|null>
     * }>
     */
    public function options(): array;

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     price_monthly: int|null,
     *     price_monthly_label: string,
     *     currency: string,
     *     limits: array<string, int|null>
     * }|null
     */
    public function find(?string $key): ?array;
}
