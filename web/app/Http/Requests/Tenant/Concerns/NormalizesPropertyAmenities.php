<?php

namespace App\Http\Requests\Tenant\Concerns;

trait NormalizesPropertyAmenities
{
    /**
     * @param  array<int, mixed>|null  $amenities
     * @return array<int, string>|null
     */
    protected function normalizeAmenities(?array $amenities): ?array
    {
        if ($amenities === null) {
            return null;
        }

        $normalized = collect($amenities)
            ->filter(fn ($value): bool => is_string($value))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->unique(fn (string $value): string => mb_strtolower($value))
            ->values()
            ->all();

        return $normalized === [] ? null : $normalized;
    }
}
