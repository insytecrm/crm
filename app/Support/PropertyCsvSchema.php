<?php

namespace App\Support;

use App\Enums\ProjectStatus;
use App\Enums\PropertyType;
use App\Models\Property;

class PropertyCsvSchema
{
    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return [
            'Project Name',
            'Developer Name',
            'Project Location',
            'RERA Number',
            'Property Type',
            'Project Status',
            'Possession Date',
            'Total Land Parcel (Acres)',
            'Total Towers',
            'Total Floors',
            'Carpet Area From (sqft)',
            'Carpet Area To (sqft)',
            'Price From',
            'Price To',
            'Tagging Period (Days)',
            'Payout Percent',
            'Sourcing Manager Name',
            'Sourcing Manager Contact',
            'Amenities',
            'Configurations',
        ];
    }

    /**
     * @return list<string>
     */
    public static function sampleRow(): array
    {
        return [
            'Skyline Towers',
            'Skyline Developers',
            'Mumbai, Maharashtra',
            'P51800001234',
            PropertyType::Apartment->label(),
            ProjectStatus::NewLaunch->label(),
            '12-2027',
            '4.5',
            '3',
            'G+B+22',
            '650',
            '1400',
            '7500000',
            '18000000',
            '90',
            '3.5',
            'Rahul Sharma',
            '+91 9876543210',
            'Swimming Pool;Gym;Clubhouse',
            '2 BHK|850|9500000|120;3 BHK|1100|12000000|80',
        ];
    }

    /**
     * @param  list<string|null>  $cells
     * @return array<string, mixed>
     */
    public static function mapImportRow(array $cells): array
    {
        $value = fn (int $index): ?string => filled($cells[$index] ?? null)
            ? trim((string) $cells[$index])
            : null;

        return [
            'project_name' => $value(0),
            'developer_name' => $value(1),
            'project_location' => $value(2),
            'rera_number' => $value(3),
            'property_type' => PropertyType::tryFromMixed($value(4))?->value,
            'project_status' => ProjectStatus::tryFromMixed($value(5))?->value,
            'possession_date' => $value(6),
            'total_land_parcel_acres' => self::nullableDecimal($value(7)),
            'total_towers' => self::nullableInteger($value(8)),
            'total_floors' => $value(9),
            'carpet_area_from_sqft' => self::nullableInteger($value(10)),
            'carpet_area_to_sqft' => self::nullableInteger($value(11)),
            'price_from' => self::nullableInteger($value(12)),
            'price_to' => self::nullableInteger($value(13)),
            'tagging_period_days' => self::nullableInteger($value(14)),
            'payout_percent' => self::nullableDecimal($value(15)),
            'sourcing_manager_name' => $value(16),
            'sourcing_manager_contact' => $value(17),
            'amenities' => self::parseAmenities($value(18)),
            'configurations' => self::parseConfigurations($value(19)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function mapExportRow(Property $property): array
    {
        return [
            $property->project_name,
            $property->developer_name,
            $property->project_location,
            $property->rera_number,
            $property->property_type?->label(),
            $property->project_status?->label(),
            $property->possession_date,
            $property->total_land_parcel_acres,
            $property->total_towers,
            $property->total_floors,
            $property->carpet_area_from_sqft,
            $property->carpet_area_to_sqft,
            $property->price_from,
            $property->price_to,
            $property->tagging_period_days,
            $property->payout_percent,
            $property->sourcing_manager_name,
            $property->sourcing_manager_contact,
            self::formatAmenities($property->amenities),
            self::formatConfigurations($property->configurations),
        ];
    }

    /**
     * @return list<string>|null
     */
    private static function parseAmenities(?string $raw): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $items = collect(explode(';', $raw))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique(fn (string $item): string => mb_strtolower($item))
            ->values()
            ->all();

        return $items === [] ? null : $items;
    }

    /**
     * @return list<array{name: string, carpet_area_sqft: ?int, price: ?int, unit_count: ?int}>|null
     */
    private static function parseConfigurations(?string $raw): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $configurations = collect(explode(';', $raw))
            ->map(function (string $entry): ?array {
                $parts = array_map(trim(...), explode('|', $entry));
                $name = $parts[0] ?? '';

                if ($name === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'carpet_area_sqft' => self::nullableInteger($parts[1] ?? null),
                    'price' => self::nullableInteger($parts[2] ?? null),
                    'unit_count' => self::nullableInteger($parts[3] ?? null),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return $configurations === [] ? null : $configurations;
    }

    /**
     * @param  array<int, string>|null  $amenities
     */
    private static function formatAmenities(?array $amenities): ?string
    {
        if ($amenities === null || $amenities === []) {
            return null;
        }

        return implode(';', $amenities);
    }

    /**
     * @param  array<int, array{name?: string, carpet_area_sqft?: mixed, price?: mixed, unit_count?: mixed}>|null  $configurations
     */
    private static function formatConfigurations(?array $configurations): ?string
    {
        if ($configurations === null || $configurations === []) {
            return null;
        }

        return collect($configurations)
            ->map(function (array $configuration): string {
                return implode('|', [
                    $configuration['name'] ?? '',
                    $configuration['carpet_area_sqft'] ?? '',
                    $configuration['price'] ?? '',
                    $configuration['unit_count'] ?? '',
                ]);
            })
            ->implode(';');
    }

    private static function nullableInteger(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private static function nullableDecimal(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
