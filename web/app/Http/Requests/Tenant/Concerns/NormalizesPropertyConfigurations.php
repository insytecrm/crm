<?php

namespace App\Http\Requests\Tenant\Concerns;

trait NormalizesPropertyConfigurations
{
    /**
     * @param  array<int, mixed>|null  $configurations
     * @return array<int, array{name: string, carpet_area_sqft: ?int, price: ?int, unit_count: ?int}>|null
     */
    protected function normalizeConfigurations(?array $configurations): ?array
    {
        if ($configurations === null) {
            return null;
        }

        $normalized = collect($configurations)
            ->filter(fn ($row): bool => is_array($row))
            ->map(function (array $row): array {
                $name = trim((string) ($row['name'] ?? ''));

                return [
                    'name' => $name,
                    'carpet_area_sqft' => $this->nullableInteger($row['carpet_area_sqft'] ?? null),
                    'price' => $this->nullableInteger($row['price'] ?? null),
                    'unit_count' => $this->nullableInteger($row['unit_count'] ?? null),
                ];
            })
            ->filter(function (array $row): bool {
                if ($row['name'] !== '') {
                    return true;
                }

                return $row['carpet_area_sqft'] !== null
                    || $row['price'] !== null
                    || $row['unit_count'] !== null;
            })
            ->values()
            ->all();

        return $normalized === [] ? null : $normalized;
    }

    protected function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
